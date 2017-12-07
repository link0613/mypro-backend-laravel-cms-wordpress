<?php

namespace AdminBundle\Controller;

use AdminBundle\Form\TemplateType;
use RestBundle\Entity\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TemplatesController extends Controller
{
    /**
     * @Route("/admin/templates", name="list_templates")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(TemplateType::class);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var Template $template */
                $template = $form->getData();
                $template->setName($template->getTemplate()->getClientOriginalName());
                $em = $this->getDoctrine()->getManager();
                $em->persist($template);
                $em->flush();

                $this->addFlash(
                    'success',
                    'Template was upload successfully'
                );

                return $this->redirectToRoute('list_templates');
            }

            $this->addFlash('error', 'Template was not upload');
        }

        $templates = $this->getDoctrine()->getRepository('RestBundle:Template')->findAll();

        return $this->render('@Admin/Admin/templates.html.twig', [
            'templates' => $templates,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/templates/download/{template}", name="template_link")
     * @param Template $template
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadAction(Template $template)
    {
        $downloadHandler = $this->get('vich_uploader.download_handler');
        $fileName   = $template->getName();

        return $downloadHandler->downloadObject($template, $fileField = 'template', $objectClass = null, $fileName);
    }

    /**
     * @Route("/admin/templates/remove/{template}", name="template_remove")
     * @Method("GET")
     * @param Template $template
     * @return Response
     */
    public function removeAction(Template $template)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($template);
        $em->flush();

        $this->addFlash(
            'success',
            'Template was delete successfully'
        );

        return $this->redirectToRoute('list_templates');
    }
}
