<?php

namespace AdminBundle\Controller;

use AdminBundle\Form\DocumentType;
use AdminBundle\Form\JobType;
use AdminBundle\Form\UserType;
use JMS\Serializer\SerializationContext;
use RestBundle\Entity\Discount;
use RestBundle\Entity\Document;
use RestBundle\Entity\Job;
use RestBundle\Entity\Message;
use RestBundle\Entity\User;
use RestBundle\Entity\UserPackages;
use RestBundle\Exception\ApiException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UsersController extends Controller
{
    /**
     * @Route("/admin/users/{page}", name="list_users", requirements={"page": "\d+"}, defaults={"page": 1})
     * @param $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($page)
    {
        $result = $this->getDoctrine()->getRepository('RestBundle:User')->getUsers($this->getUser(), $page);
        $admins = $this->getDoctrine()->getRepository('RestBundle:User')->getAdminsForAssignToUser();

        return $this->render('@Admin/Admin/users.html.twig', [
            'users' => $result['users'],
            'pages' => $result['count'],
            'current' => $page,
            'admins' => $admins
        ]);
    }

    /**
     * @Route("/admin/user/{user}", name="view_user")
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(User $user)
    {
        $form = $this->createForm(UserType::class, $user);
        $file_form = $this->createForm(DocumentType::class);
        $em = $this->getDoctrine()->getManager();
        $messages = $em->getRepository('RestBundle:Message')->getUserMessages($user);
        $data = [];

        foreach ($messages as $message) {
            /** @var Message $message */
            $message->setIsUnread(false);
            $em->persist($message);

            $data[$message->getDate()->format('Y-d-m')][] = $message;
        }

        /** @var UserPackages[] $user_packages */
        $user_packages = $user->getPackages();

        foreach ($user_packages as $user_package) {
            if (null !== $user_package->getDiscount()) {
                $discount = $em-> getRepository('RestBundle:Discount')->findOneBy(['code' => $user_package->getDiscount()]);

                if (!$discount instanceof Discount) {
                    continue;
                }
            }
        }

        $em->flush();

        return $this->render('@Admin/Admin/user_editor.html.twig', [
            'form' => $form->createView(),
            'file_form' => $file_form->createView(),
            'user' => $user,
            'messages' => $data
        ]);
    }

    /**
     * @Route("/admin/user/{user}/jobs", name="user_jobs")
     * @param User $user
     * @return Response
     */
    public function userJobAction(User $user)
    {
        $form = $this->createForm(JobType::class);

        /** @var Job[] $jobs */
        $em = $this->getDoctrine()->getManager();
        $jobs = $em->getRepository('RestBundle:Job')->getUserJobs($user);
        $jobs_liked = [];
        $jobs_applied = [];

        foreach ($jobs as $job) {
            if ($job->getSection() === 'liked') {
                $jobs_liked[] = $job;
            } else {
                $jobs_applied[] = $job;
            }

            $job->setIsNewAdmin(false);
            $em->persist($job);
        }

        $em->flush();

        return $this->render('@Admin/Admin/view_jobs.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'jobs_liked' => $jobs_liked,
            'jobs_applied' => $jobs_applied
        ]);
    }

    /**
     * @Route("/admin/cover/{job}", name="user_get_cover_letter")
     * @Method("GET")
     * @param Job $job
     * @return Response
     */
    public function downloadCoverLetterAction(Job $job)
    {
        $downloadHandler = $this->get('vich_uploader.download_handler');
        $fileName   = $job->getAttachmentAlias();

        return $downloadHandler->downloadObject($job, $fileField = 'attachment', $objectClass = null, $fileName);
    }

    /**
     * @Route("/admin/cover/{job}", name="user_add_cover_letter")
     * @Method("POST")
     * @param Request $request
     * @param Job $job
     * @return Response
     * @throws ApiException
     */
    public function addCoverLetterAction(Request $request, Job $job)
    {
        if ($request->files->has('file')) {
            /** @var UploadedFile $uploaded_file */
            $uploaded_file = $request->files->get('file');
            /** @var File $file */
            $file = $this->get('app.file_uploader')->upload($uploaded_file);

            $job->setAttachment($file);
            $job->setAttachmentAlias($uploaded_file->getClientOriginalName());
            $job->setAttachmentName($file->getFilename());

            $em = $this->getDoctrine()->getManager();
            $em->persist($job);

            $errors = $this->get('validator')->validate($job);

            if (count($errors) > 0) {
                $this->get('app.file_uploader')->remove($file);
                throw new ApiException($errors[0]->getMessage());
            }

            $em->flush();
        } else {
            throw new ApiException('No file');
        }

        return new JsonResponse([
            'id' => $job->getId(),
            'file_url' => $this->generateUrl('user_get_cover_letter', ['job' => $job->getId()]),
            'remove_url' => $this->generateUrl('user_remove_cover_letter', ['job' => $job->getId()]),
            'add_url' => $this->generateUrl('user_add_cover_letter', ['job' => $job->getId()]),
            'filename' => $job->getAttachmentAlias()
        ]);
    }

    /**
     * @Route("/admin/cover/{job}/remove", name="user_remove_cover_letter")
     * @Method("GET")
     * @param Job $job
     * @return Response
     * @throws ApiException
     */
    public function removeCoverLetterAction(Job $job)
    {
        $job->setAttachment(null);
        $job->setAttachmentAlias(null);
        $job->setAttachmentName(null);

        $em = $this->getDoctrine()->getManager();
        $em->persist($job);

        $em->flush();

        return new JsonResponse(['Ok']);
    }

    /**
     * @Route("/admin/user/{user}/job/add", name="add_user_job")
     * @Method("POST")
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addJobAction(Request $request, User $user)
    {
        $job = new Job();
        $form = $this->createForm(JobType::class, $job);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (strpos($form->getData()->getLink(), 'http') === false) {
                $job->setLink('http://' . $form->getData()->getLink());
            }

            $job->setUser($user);
            $job->setAddedBy('admin');
            $em = $this->getDoctrine()->getManager();
            $em->persist($job);
            $em->flush();

            $this->addFlash(
                'success',
                'Job was added successfully'
            );
        } else {
            $errors = $this->get('validator')->validate($job);

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->redirectToRoute('user_jobs', ['user' => $user->getId()]);
    }

    /**
     * @Route("/admin/job/status", name="change_job_status")
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeStatusJobAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            /** @var Job $job */
            $job = $em->getRepository('RestBundle:Job')->find($request->get('job'));

            $status = $request->get('status');

            if ($status === 'Applied') {
                $job->setSection('applied');
                $job->setAppliedDate(new \DateTime());
            } elseif ($status === 'Ready' || $status === 'Pending') {
                $job->setSection('liked');
            }

            $job->setStatus($status, true);
            $em->persist($job);
            $em->flush();

            if ($status === 'Applied') {
                $user = $job->getUser();

                return $this->redirectToRoute('user_jobs', ['user' => $user->getId()]);
            }

            return new JsonResponse(['status' => 'Ok']);
        }

        return new JsonResponse(['status' => 'Fail']);
    }

    /**
     * @Route("/admin/user/{user}/documents/add", name="admin_user_documents_add")
     * @Method("POST")
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addDocumentAction(Request $request, User $user)
    {
        $documents = new Document();
        $form = $this->createForm(DocumentType::class, $documents);
        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var User $admin */
            $admin = $this->getUser();
            $documents->setName($documents->getDocument()->getClientOriginalName());
            $documents->setAddedBy($admin->getFullName());
            $profile = $user->getProfile();
            $documents->setProfile($profile);
            $profile->addDocument($documents);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'success',
                'Document was upload successfully'
            );
        } else {
            $errors = $this->get('validator')->validate($documents);

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->redirectToRoute('view_user', ['user' => $user->getId()]);
    }

    /**
     * @Route("/admin/document/download/{document}", name="admin_user_documents_download")
     * @Method("GET")
     * @param Document $document
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadFileAction(Document $document)
    {
        $downloadHandler = $this->get('vich_uploader.download_handler');
        $fileName   = $document->getName();

        return $downloadHandler->downloadObject($document, $fileField = 'document', null, $fileName);
    }

    /**
     * @Route("/admin/document/remove/{document}", name="admin_user_documents_remove")
     * @Method("GET")
     * @param Document $document
     * @return Response
     */
    public function removeFileAction(Document $document)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $document->getProfile()->getUser();
        $em->remove($document);
        $em->flush();

        $this->addFlash(
            'success',
            'Document was delete successfully'
        );

        return $this->redirectToRoute('view_user', ['user' => $user->getId()]);
    }

    /**
     * @Route("/admin/assign/user", name="admin_user_assign_to_admin")
     * @Method("POST")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function assignAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('RestBundle:User')->find($request->get('user'));
            $admin = $em->getRepository('RestBundle:User')->find($request->get('admin'));
            $user->setAdmin($admin);

            $em->persist($user);
            $em->flush();

            return new JsonResponse(['status' => 'Ok']);
        }

        return new JsonResponse(['status' => 'Fail'], JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/admin/message/send/{user}", name="admin_message_send")
     * @param Request $request
     * @param User $user
     * @return Response
     * @throws ApiException
     */
    public function sendMessageAction(Request $request, User $user)
    {
        if ($request->isXmlHttpRequest()) {
            $link = $delete_link = null;
            $message = new Message();
            /** @var User $admin */
            $admin = $user->getAdmin();
            $message->setAuthor($admin->getId());
            $message->setRecipient($user->getId());
            $message->setTypeSender(1);
            $em = $this->getDoctrine()->getManager();

            $post_message = trim($request->get('message'));

            if (!empty($post_message)) {
                $message->setMessage($post_message);
                $em->persist($message);
                $em->flush();
            } else if ($request->files->has('attachment')) {
                /** @var UploadedFile $uploaded_file */
                $uploaded_file = $request->files->get('attachment');

                /** @var File $file */
                $file = $this->get('app.file_uploader')->upload($uploaded_file);

                $message->setAttachment($file);
                $message->setAttachmentName($uploaded_file->getClientOriginalName());
                $message->setAttachmentPath($file->getFilename());
                $em->persist($message);

                $errors = $this->get('validator')->validate($message);

                if (count($errors) > 0) {
                    $this->get('app.file_uploader')->remove($file);
                    throw new ApiException($errors[0]->getMessage());
                }

                $em->flush();

                $link = $this->generateUrl('admin_chat_attachment_download', ['message' => $message->getId()]);
                $delete_link = $this->generateUrl('admin_message_delete', ['message' => $message->getId()]);
            }

            return new JsonResponse([
                'message' => $message->getMessage(),
                'message_id' => $message->getId(),
                'download_link' => $link,
                'attachment_name' => $message->getAttachmentName(),
                'delete_link' => $delete_link
            ]);
        }

        return new JsonResponse(['status' => 'Fail', 'message'], JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/admin/message/get/{user}", name="admin_message_get")
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function getMessageAction(Request $request, User $user)
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();

            $messages = $em->getRepository('RestBundle:Message')->findBy([
                'author' => $user->getId(),
                'is_unread' => true
            ]);

            $data = [];

            foreach ($messages as $message) {
                /** @var Message $message */
                $message->setIsUnread(false);
                $em->persist($message);

                if ($message->getAttachmentName()) {
                    $link = $this->generateUrl('admin_chat_attachment_download', ['message' => $message->getId()]);
                    $delete_link = $this->generateUrl('admin_message_delete', ['message' => $message->getId()]);

                    $message = [
                        'message_id' => $message->getId(),
                        'download_link' => $link,
                        'attachment_name' => $message->getAttachmentName(),
                        'delete_link' => $delete_link
                    ];
                }

                $data[] = $message;
            }

            $em->flush();

            if (empty($data)) {
                return new JsonResponse([]);
            }

            return new JsonResponse($this->get('serializer')->serialize($data, 'json', SerializationContext::create()->setGroups(['chat'])));
        }

        return new JsonResponse(['status' => 'Fail'], JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/admin/chat/download/{message}", name="admin_chat_attachment_download")
     * @Method("GET")
     * @param Message $message
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadAttachmentAction(Message $message)
    {
        $downloadHandler = $this->get('vich_uploader.download_handler');
        $fileName   = $message->getAttachmentName();

        return $downloadHandler->downloadObject($message, $fileField = 'attachment', null, $fileName);
    }

    /**
     * @Route("/admin/order/{order}/{status}", requirements={"status": "completed|cancel"}, name="admin_order_status")
     * @Method("GET")
     * @param UserPackages $order
     * @param $status
     * @return Response
     */
    public function changeOrderStatusAction(UserPackages $order, $status)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $order->getUser();

        if ($status === 'completed') {
            $order->setIsApproved(true);
            $this->get('user.mailer')->sendAdminLeaveReview($user);

            $this->addFlash(
                'success',
                'Order was approved successfully'
            );
        } else {
            $uuid = $order->getUuid();
            if ($uuid) {
                $schedule = $em->getRepository('RestBundle:Schedule')->findOneBy(['uuid' => $uuid]);
                $em->remove($schedule);
            }

            $em->remove($order);

            $this->addFlash(
                'success',
                'Order was delete successfully'
            );
        }

        $em->flush();

        return $this->redirectToRoute('view_user', ['user' => $user->getId()]);
    }

    /**
     * @Route("/admin/users/delete/{user}", name="delete_user")
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function deleteAction(User $user)
    {
        if (!$this->getUser()->isSuperAdmin()) {
            throw new \Exception('Access denied');
        }

        $em = $this->getDoctrine()->getManager();
        $user->setProfile(null);
        $em->remove($user);

        $em->flush();

        $this->addFlash(
            'success',
            'User was deleted successfully.'
        );

        return $this->redirectToRoute('list_users');
    }
}
