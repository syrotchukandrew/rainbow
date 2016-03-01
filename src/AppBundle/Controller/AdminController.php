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
    public function showAction(Estate $estate, $slug)
    {
        $estate = $this->getDoctrine()->getRepository('AppBundle:Estate')->findOneBy(array('slug' => $slug));
        $deleteForm = $this->createDeleteForm($estate);
        return $this->render('@App/admin/show_estate.html.twig', array(
            'estate'        => $estate,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/estate/delete/{slug}", name="admin_estate_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Estate $estate)
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
            $uploadableManager = $this->container->get('stof_doctrine_extensions.uploadable.manager');
            $files = $request->files->get('app_bundle_estate_type');
            if ($files['imageFile'][0] !== null) {
                foreach ($files['imageFile'] as $imageData) {
                    $image = new File();
                    $uploadableManager->markEntityToUpload($image, $imageData);
                    $image->setEstate($estate);
                    $estate->addFile($image);
                    $entityManager->persist($image);
                }
            }
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
