<?php

namespace AdminBundle\Controller;

use AdminBundle\Form\EditorType;
use RestBundle\Entity\Blog;
use SitemapPHP\Sitemap;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BlogController
 * @package AdminBundle\Controller
 */
class BlogController extends Controller
{
    /**
     * @Route("/admin/blogs/add", name="add_blog")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $blog = new Blog();
        $blog->addCategory($this->getDoctrine()->getRepository('RestBundle:Category')->find(1));
        $form = $this->createForm(EditorType::class, $blog);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid() && !$form->get('content')->isEmpty()) {
                $blog->addUrl();
                $blog->setAdmin($this->getUser());

                $em = $this->getDoctrine()->getManager();
                $em->persist($blog);
                $em->flush();

                $this->addFlash(
                    'success',
                    'Blog was added successfully'
                );

                $this->get('sitemap.generator')->generate();

                return $this->redirectToRoute('edit_blog', ['blog' => $blog->getId()]);
            }

            $this->addFlash('error', 'Content can\'t be empty');

            $errors = $this->get('validator')->validate($blog);

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('@Admin/Admin/editor.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/blogs/edit/{blog}", name="edit_blog", requirements={"page": "\d+"}, defaults={"page": 1})
     * @param Request $request
     * @param Blog $blog
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function editAction(Request $request, Blog $blog)
    {
        $form = $this->createForm(EditorType::class, $blog, [
            'disable_submit' => $blog->getAdmin() !== $this->getUser() && $this->getUser()->getRole() === 'ROLE_MANAGER_BLOG'
        ]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($blog->getAdmin() !== $this->getUser() && $this->getUser()->getRole() === 'ROLE_MANAGER_BLOG') {
                throw new \Exception('Access denied');
            }

            if ($form->isValid() && !$form->get('content')->isEmpty()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($blog);

                $errors = $this->get('validator')->validate($blog->getAuthor());

                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $this->addFlash('error', $error->getMessage());
                    }
                } else {
                    $em->flush();

                    $this->addFlash(
                        'success',
                        'Your changes has been saved'
                    );

                    $this->get('sitemap.generator')->generate();
                }

                return $this->redirect($request->getRequestUri());
            }

            $errors = $this->get('validator')->validate($blog->getAuthor());

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('@Admin/Admin/editor.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/blogs/delete/{blog}", name="delete_blog", requirements={"page": "\d+"}, defaults={"page": 1, "filter": null})
     * @Security("has_role('ROLE_ADMIN_MANAGER')")
     * @param Request $request
     * @param Blog $blog
     * @return Response
     */
    public function deleteAction(Request $request, Blog $blog)
    {
        $blog->remove();

        $this->getDoctrine()->getManager()->flush();

        $this->addFlash(
            'success',
            $blog->getTitle() . ' was deleted successfully.'
        );

        $this->get('sitemap.generator')->generate();
        $filter = $request->get('filter');
        $page = $request->get('page');

        return $this->getBlogPage($request, $page, $filter);
    }

    /**
     * @Route("/admin/blogs/{page}/{filter}", requirements={"page":"\d+"}, defaults={"page": 1, "filter": null}, name="view_list_blog")
     * @param Request $request
     * @param $page
     * @param $filter
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, $page, $filter)
    {
        if ($request->isMethod('POST')) {
            if ($request->request->has('filter')) {
                $filter = $request->request->get('filter');
            }

            $page = 1;
        }

        return $this->getBlogPage($request, $page, $filter);
    }

    /**
     * @param $page
     * @param $filter
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getBlogPage($request, $page, $filter)
    {
        $em = $this->getDoctrine()->getManager();

        $sortField = $request->query->get('sort_field', 'post_date');
        $sortOrder = $request->query->get('sort_order', 'DESC');

        if (!empty($filter)) {
            if ($filter === 'top-category') {
                $blogs = $em->getRepository('RestBundle:Blog')->getCareerAdvicesTopCategory($page, 15, $sortField, $sortOrder);
            } else {
                $category = $em->getRepository('RestBundle:Category')->findOneBy(['category' => $filter]);
                $blogs = $em->getRepository('RestBundle:Blog')->getCareerAdvicesByCategory($category, $page, 15, $sortField, $sortOrder);
            }
        } else {
            if ($query = $request->get('query')) {
                $blogs = $this->getDoctrine()->getRepository('RestBundle:Blog')->getCareerAdvicesByQuery($query, $page, 15, $sortField, $sortOrder);
            } else {
                $blogs = $this->getDoctrine()->getRepository('RestBundle:Blog')->getCareerAdvices($page, 15, $sortField, $sortOrder);
            }
        }


        return $this->render('@Admin/Admin/blogs.html.twig', [
            'pages' => $blogs['count'],
            'current' => $page,
            'filter' => $filter,
            'blogs' => $blogs['blogs']
        ]);
    }

    /**
     * @Route("/admin/upload/image/", name="upload_blog_image")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function uploadBlogImageAction(Request $request)
    {
        if (!$file = $request->files->get('upload')) {
            throw new \Exception('File not found');
        }

        $file = $this->get('app.file_uploader')->uploadImage($file);

        $funcNum = $request->get('CKEditorFuncNum');
        $fileUrl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . "/uploads/files/images/" . $file->getFileName();

        $content= sprintf("<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction(%s, '%s');</script>", $funcNum, $fileUrl);

        return new Response($content, 200, array('content-type' => 'text/html'));
    }
}
