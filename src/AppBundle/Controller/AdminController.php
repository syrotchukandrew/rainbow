<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Estate;
use AppBundle\Entity\File;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Form\EstateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="admin_index")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('AppBundle::admin/index.html.twig', array(
            'base_dir' => realpath($this->getParameter('kernel.root_dir') . '/..'),
        ));
    }

    /**
     * @Route("/estates", name="admin_estates")
     * @Method("GET")
     */
    public function estatesAction(Request $request)
    {
        $estates = $this->getDoctrine()->getRepository('AppBundle:Estate')->findAll();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $estates,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('@App/admin/estates.html.twig', array('pagination' => $pagination));
    }

    /**
     * @Route("/estate/show/{slug}", name="admin_estate_show")
     * @Method("GET")
     */
    public function estateShowAction(Estate $estate, $slug)
    {
        $estate = $this->getDoctrine()->getRepository('AppBundle:Estate')->findOneBy(array('slug' => $slug));
        $deleteForm = $this->createDeleteForm($estate);
        return $this->render('@App/admin/show_estate.html.twig', array(
            'estate'        => $estate,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/estate/edit/{slug}", name="admin_estate_edit")
     * @Method({"GET", "POST"})
     */
    public function estateEditAction(Estate $estate, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $editForm = $this->createForm(EstateType::class, $estate);
        $deleteForm = $this->createDeleteForm($estate);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $estate = $this->get('app.file_manager')->fileManager($estate);
            $entityManager->persist($estate);
            $entityManager->flush();
            return $this->redirectToRoute('admin_estate_show', array('slug' => $estate->getSlug()));
        }
        return $this->render('@App/admin/edit_estate.html.twig', array(
            'estate'        => $estate,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/estate/delete/{slug}", name="admin_estate_delete")
     * @Method("DELETE")
     */
    public function estateDeleteAction(Request $request, Estate $estate)
    {
        $form = $this->createDeleteForm($estate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($estate);
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_index');
    }

    private function createDeleteForm(Estate $estate)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_estate_delete', array('slug' => $estate->getSlug())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }


    /**
     * @Route("/estate/new", name="admin_estate_new")
     * @Method({"GET", "POST"})
     */
    public function newEstateAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $estate = new Estate();
        //$this->denyAccessUnlessGranted('create', $estate);
        $form = $this->createForm(EstateType::class, $estate)->add('saveAndCreateNew', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $estate = $this->get('app.file_manager')->fileManager($estate);
            $entityManager->persist($estate);
            $entityManager->flush();
            $nextAction = $form->get('saveAndCreateNew')->isClicked()
                ? 'admin_estate_new'
                : 'admin_index';
            return $this->redirectToRoute($nextAction);
        }
        return $this->render('@App/admin/new_estate.html.twig', array(
            'estate' => $estate,
            'form' => $form->createView(),
        ));
    }
}
