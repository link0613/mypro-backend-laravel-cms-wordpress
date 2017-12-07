<?php
namespace RestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable
 * @ORM\Entity(repositoryClass="RestBundle\Entity\Repository\MessageRepository")
 * @ORM\Table(name="messages")
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $author;

    /**
     * @ORM\Column(type="integer")
     */
    protected $recipient;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $message;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * @Assert\File(maxSize="10M",
     *     mimeTypes={
     *     "application/pdf",
     *     "application/msword",
     *     "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
     *     "application/rtf",
     *     "text/rtf",
     *     "text/plain",
     *     "image/png",
     *     "image/jpeg"
     * },
     *     mimeTypesMessage="Available file types pdf, doc(x), rtf, txt, png, jpg, jpeg.",
     *     maxSizeMessage="The file can not be larger than 10 Mb.")
     * @Vich\UploadableField(mapping="document", fileNameProperty="attachment_path")
     */
    protected $attachment;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $attachment_path;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $attachment_name;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $is_unread = 1;

    /**
     * @ORM\Column(type="integer")
     *
     * 1 - admin
     * 2 - user
     */
    protected $type_sender;

    protected $username;

    public function __construct()
    {
        $this->date = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param mixed $recipient
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getIsUnread()
    {
        return $this->is_unread;
    }

    /**
     * @param mixed $is_unread
     */
    public function setIsUnread($is_unread)
    {
        $this->is_unread = $is_unread;
    }

    /**
     * @return mixed
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @param File $attachment
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * @return mixed
     */
    public function getAttachmentPath()
    {
        return $this->attachment_path;
    }

    /**
     * @param mixed $attachment_path
     */
    public function setAttachmentPath($attachment_path)
    {
        $this->attachment_path = $attachment_path;
    }

    /**
     * @return mixed
     */
    public function getAttachmentName()
    {
        return $this->attachment_name;
    }

    /**
     * @param mixed $attachment_name
     */
    public function setAttachmentName($attachment_name)
    {
        $this->attachment_name = $attachment_name;
    }

    /**
     * @return mixed
     */
    public function getTypeSender()
    {
        return $this->type_sender;
    }

    /**
     * @param mixed $type_sender
     */
    public function setTypeSender($type_sender)
    {
        $this->type_sender = $type_sender;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }
}