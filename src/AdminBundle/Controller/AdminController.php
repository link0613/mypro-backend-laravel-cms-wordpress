<?php

namespace AdminBundle\Controller;

use AdminBundle\Form\AdminType;
use RestBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    /**
     * @Route("/admin/admins/add", name="add_admins")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        $form = $this->createForm(AdminType::class);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $admin = $form->getData();


            if ($form->isValid()) {
                $admin->setUsername($admin->getEmail());
                $admin->addRole('ROLE_ADMIN');

                $plainPassword = $admin->getPassword();

                $em = $this->getDoctrine()->getManager();
                $em->persist($admin);
                $em->flush();

                $this->addFlash(
                    'success',
                    'Admin was added successfully'
                );

                $this->get('user.mailer')->sendAdminAccess($admin, $plainPassword);

                return $this->redirectToRoute('view_admin', ['admin' => $admin->getId()]);
            }

            $errors = $this->get('validator')->validate($admin);

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('@Admin/Admin/admin_form.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/admin/admins/delete/{admin}", name="delete_admin")
     * @param User $admin
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(User $admin)
    {
        if (!$admin->isSuperAdmin()) {
            $em = $this->getDoctrine()->getManager();
            /** @var User[] $users */
            $users = $admin->getUsers();
            /** @var Blog[] $blogs */
            $blogs = $admin->getBlogs();

            if (count($users) > 0) {
                $superAdmin = $em->getRepository('RestBundle:User')->getSuperAdmin();

                foreach ($users as $user) {
                    $user->setAdmin($superAdmin);
                    $em->persist($user);
                }
            }

            if (count($blogs) > 0) {
                $superAdmin = $em->getRepository('RestBundle:User')->getSuperAdmin();

                foreach ($blogs as $blog) {
                    $blog->setAdmin($superAdmin);
                }
            }

            $em->remove($admin);

            $em->flush();

            $this->addFlash(
                'success',
                'Admin was deleted successfully.'
            );
        } else {
            $this->addFlash(
                'error',
                'You can\'t delete a Super Admin.'
            );
        }

        return $this->redirectToRoute('dashboard');
    }

    /**
     * @Route("/admin/admins/{admin}", name="view_admin")
     * @param Request $request
     * @param User $admin
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, User $admin)
    {
        $form = $this->createForm(AdminType::class, $admin);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $admin->setUsername($admin->getEmail());
                $em->persist($admin);
                $em->flush();

                $this->addFlash(
                    'success',
                    'Your changes has been saved'
                );

                return $this->redirect($request->getRequestUri());
            }

            $errors = $this->get('validator')->validate($admin);

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('@Admin/Admin/admin_form.html.twig', array('form' => $form->createView()));
    }
}
