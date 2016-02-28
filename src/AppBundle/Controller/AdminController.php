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
use Gedmo\Uploadable\FileInfo\FileInfoArray;


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
     * @Route("/newestate", name="admin_new_estate")
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
            }}
            $entityManager->persist($estate);
            $entityManager->flush();
            $nextAction = $form->get('saveAndCreateNew')->isClicked()
                ? 'admin_new_estate'
                : 'admin_index';
            return $this->redirectToRoute($nextAction);
        }
        return $this->render('@App/admin/newestate.html.twig', array(
            'estate' => $estate,
            'form' => $form->createView(),
        ));
    }
}
