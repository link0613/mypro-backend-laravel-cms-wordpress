<?php

namespace AdminBundle\Controller;

use JMS\Serializer\SerializationContext;
use RestBundle\Entity\Message;
use RestBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    /**
     * @Route("/admin/message/count", name="admin_message_get_count")
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function indexAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();

            /** @var Message[] $messages */
            $messages = $em->getRepository('RestBundle:Message')->findBy([
                'recipient' => $this->getUser()->getId(),
                'is_unread' => true
            ]);

            if (empty($messages)) {
                return new JsonResponse([]);
            }

            $data = [];

            foreach ($messages as $message) {
                /** @var User $author */
                $author = $em->getRepository('RestBundle:User')->find($message->getAuthor());
                $message->setUsername($author->getFullName());

                $data[$author->getId()][] = $message;
            }

            return new Response($this->get('serializer')->serialize($data, 'json', SerializationContext::create()->setGroups(['api'])));
        }

        return new JsonResponse(['status' => 'Fail'], JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * @param Request $request
     * @param Message $message
     * @return JsonResponse
     *
     * @Route("/admin/message/{message}/delete", name="admin_message_delete")
     */
    public function deleteAction(Request $request, Message $message)
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($message);
            $em->flush();

            return new JsonResponse(['status' => 'Success'], JsonResponse::HTTP_OK);
        }

        return new JsonResponse(['status' => 'Fail'], JsonResponse::HTTP_BAD_REQUEST);
    }
}
