<?php

namespace RestBundle\Services;

use Fetch\Server;
use Fetch\Message as ImapMessage;
use RestBundle\Entity\Message;
use RestBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImapService
{
    protected $container;
    protected $server;

    public function __construct(ContainerInterface $container)
    {
        $email_server = $container->getParameter('mailer_host');
        $auth_user = $container->getParameter('noreply_email');
        $auth_pass = $container->getParameter('noreply_pass');
        $server = new Server($email_server, 143);
        $server->setAuthentication($auth_user, $auth_pass);
        $this->container = $container;
        $this->server = $server;
    }

    /**
     * @return Message[]|array
     */
    public function import()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository('RestBundle:User');

        /** @var ImapMessage[] $messages */
        $messages = $this->server->getMessages();
        $msgs = [];

        foreach ($messages as $message) {
            preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $message->getSubject(), $matches);

            if (!isset($matches[0])) {
                $message->setFlag('Deleted');
                continue;
            }

            $to = $matches[0];
            $from = $message->getAddresses('from')['address'];

            /** @var User $author */
            $author = $userRepository->findOneBy(['email' => $from]);

            if (!$author instanceof User) {
                $message->setFlag('Deleted');
                continue;
            }

            if (!$author->hasRole('ROLE_ADMIN')) {
                $recipient = $author->getAdmin();
            } else {
                $recipient = $userRepository->findOneBy(['email' => $to]);
            }

            if (!$recipient instanceof User) {
                $message->setFlag('Deleted');
                continue;
            }

            $text = explode('> On ' . date('D'), $message->getMessageBody());

            if (count($text) === 1) {
                $text = explode('On ' . date('D'), $message->getMessageBody());
            }

            if (count($text) === 1) {
                $text = explode('> On ' . date('M'), $message->getMessageBody());
            }

            if (count($text) === 1) {
                $text = explode('On ' . date('M'), $message->getMessageBody());
            }

            if (count($text) === 1) {
                $text = explode(date('Y-m-d'), $message->getMessageBody());
            }

            if (count($text) === 1) {
                $text = explode(date('d'), $message->getMessageBody());
            }

            $message->setFlag('Deleted');

            if (trim($text[0])) {
                $msg = new Message();
                $msg->setAuthor($author->getId());
                $msg->setRecipient($recipient->getId());
                $msg->setMessage($text[0]);
                $msg->setTypeSender($author->hasRole('ROLE_ADMIN') ? 1 : 2);
                $msg->setDate(new \DateTime(date('Y:m:d H:i:s', $message->getDate())));

                $msgs[] = $msg;
            }

            if ($attachments = $message->getAttachments()) {
                $uploadDir = $this->container->getParameter('upload_dir');
                foreach ($attachments as $attachment) {
                    $ext = false;
                    if ($originFilename = $attachment->getFilename()) {
                        try {
                            list($name, $ext) = explode('.', $originFilename);
                        } catch(\Exception $e) {
                        }
                    }

                    $filename = $ext ? md5(uniqid()) . '.' . $ext : md5(uniqid());
                    $filePath = $uploadDir . '/' . $filename;

                    $attachment->saveAs($filePath);

                    $msg = new Message();
                    $msg->setAuthor($author->getId());
                    $msg->setRecipient($recipient->getId());
                    $msg->setTypeSender($author->hasRole('ROLE_ADMIN') ? 1 : 2);
                    $msg->setDate(new \DateTime(date('Y:m:d H:i:s', $message->getDate())));
                    $msg->setAttachmentName($originFilename ? $originFilename : $filename);
                    $msg->setAttachmentPath($filename);

                    /**@todo Need to add validation*/
                    $msgs[] = $msg;
                }
            }
        }

        $this->server->expunge();

        return $msgs;
    }

    public function save($mailbox, $msg)
    {
        if (!$this->server->hasMailBox('INBOX.' . $mailbox)) {
            $this->server->createMailBox('INBOX.' . $mailbox);
        }

        imap_append($this->server->getImapStream(), 'INBOX.' . $mailbox, $msg);
    }
}