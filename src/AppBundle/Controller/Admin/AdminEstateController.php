<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Estate;
use AppBundle\Entity\File;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Form\EstateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use AppBundle\Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Security("has_role('ROLE_MANAGER')")
 * @Route("/admin")
 */
class AdminEstateController extends Controller
{
    /**
     * @Route("/", name="admin_index")
     */
    public function indexAction(Request $request)
    {
        $users = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();
        $districts = $this->getDoctrine()->getRepository('AppBundle:District')->findAll();
        $comments = $this->getDoctrine()->getRepository('AppBundle:Comment')->getDisabledComments();
        $estates = $this->getDoctrine()->getRepository('AppBundle:Estate')->findAll();
        return $this->render('AppBundle::admin/index.html.twig', array(
            'count_disabled_comments' => count($comments),
            'count_estates' => count($estates),
            'count_users' => count($users),
            'count_districts' => count($districts),
        ));
    }

    /**
     * @Route("/estates", name="admin_estates")
     * @Method("GET")
     */
    public function estatesAction(Request $request)
    {
        $estates = $this->getDoctrine()->getRepository('AppBundle:Estate')->getEstatesWithAll();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $estates,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('@App/admin/estate/estates.html.twig', array('pagination' => $pagination));
    }

    /**
     * @Route("/estate/show/{slug}", name="admin_estate_show")
     * @Method("GET")
     */
    public function estateShowAction($slug, Request $request)
    {
        $estate = $this->getDoctrine()->getRepository('AppBundle:Estate')->getOneEstateWithAll($slug);
        $deleteForm = $this->createDeleteForm($estate);
        return $this->render('@App/admin/estate/show_estate.html.twig', array(
            'estate' => $estate,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/estate/new", name="admin_estate_new")
     * @Method({"GET", "POST"})
     */
    public function newEstateAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $estate = new Estate();
        $finalCategories = $this->container->get('app.final_category_finder')->findFinalCategories();
        $this->denyAccessUnlessGranted('create', $estate);
        $form = $this->createForm(EstateType::class, $estate, array('categories_choices' => $finalCategories))
            ->add('saveAndCreateNew', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $estate = $this->get('app.file_manager')->fileManager($estate);
            //$this->get('app.check_floor')->setFirstLastFloor($estate);
            $entityManager->persist($estate);
            $entityManager->flush();
            $nextAction = $form->get('saveAndCreateNew')->isClicked()
                ? 'admin_estate_new'
                : 'admin_estates';
            return $this->redirectToRoute($nextAction);
        }
        return $this->render('@App/admin/estate/new_estate.html.twig', array(
            'form' => $form->createView(),
            'estate' => $estate
        ));
    }

    /**
     * @Route("/estate/edit/{slug}", name="admin_estate_edit")
     * @Method({"GET", "POST"})
     */
    public function estateEditAction($slug, Request $request)
    {
        $estate = $this->getDoctrine()->getRepository('AppBundle:Estate')->getOneEstateWithAll($slug);
        $entityManager = $this->getDoctrine()->getManager();
        $this->denyAccessUnlessGranted('edit', $estate);
        $finalCategories = $this->container->get('app.final_category_finder')->findFinalCategories();
        $editForm = $this->createForm(EstateType::class, $estate, array(
            'categories_choices' => $finalCategories, 'isDeleteImages' => true));
        $deleteForm = $this->createDeleteForm($estate);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $estate = $this->get('app.file_manager')->fileManager($estate);
            $entityManager->persist($estate);
            $entityManager->flush();
            return $this->redirectToRoute('admin_estate_show', array('slug' => $estate->getSlug()));
        }
        return $this->render('@App/admin/estate/edit_estate.html.twig', array(
            'estate' => $estate,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/estate/delete/{slug}", name="admin_estate_delete")
     * @Method("DELETE")
     * @Security("is_granted('remove', estate)")
     * @ParamConverter("estate", options={"mapping": {"slug": "slug"}})
     */
    public function estateDeleteAction(Request $request, Estate $estate)
    {
        $this->denyAccessUnlessGranted('remove', $estate);
        //$cacheManager = $this->container->get('liip_imagine.cache.manager');
        $form = $this->createDeleteForm($estate);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($estate);
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_estates');
    }

    private function createDeleteForm(Estate $estate)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_estate_delete', array('slug' => $estate->getSlug())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
