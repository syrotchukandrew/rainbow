<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\District;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Form\DistrictType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


/**
 * @Security("has_role('ROLE_ADMIN')")
 * @Route("/admin")
 */
class AdminDistrictController extends Controller
{
    /**
     * @Route("/districts", name="admin_districts")
     * @Method("GET")
     */
    public function districtsAction(Request $request)
    {
        $districts = $this->getDoctrine()->getRepository('AppBundle:District')->findAll();
        return $this->render('@App/admin/district/districts.html.twig', array('districts' => $districts));
    }

    /**
     * @Route("/district/show/{slug}", name="admin_district_show")
     * @Method("GET")
     * @ParamConverter("district", options={"mapping": {"slug": "slug"}})
     */
    public function districtShowAction(District $district, Request $request)
    {
        $deleteForm = $this->createDeleteForm($district);
        return $this->render('@App/admin/district/show_district.html.twig', array(
            'district'        => $district,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/district/new", name="admin_district_new")
     * @Method({"GET", "POST"})
     */
    public function newDistrictAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $district = new District();
        //$this->denyAccessUnlessGranted('create', $estate);
        $form = $this->createForm(DistrictType::class, $district)->add('saveAndCreateNew', SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($district);
            $entityManager->flush();
            $nextAction = $form->get('saveAndCreateNew')->isClicked()
                ? 'admin_district_new'
                : 'admin_districts';
            return $this->redirectToRoute($nextAction);
        }
        return $this->render('@App/admin/district/new_district.html.twig', array(
            'district' => $district,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/district/edit/{slug}", name="admin_district_edit")
     * @Method({"GET", "POST"})
     * @ParamConverter("district", options={"mapping": {"slug": "slug"}})
     */
    public function districtEditAction(District $district, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $editForm = $this->createForm(DistrictType::class, $district);
        $deleteForm = $this->createDeleteForm($district);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager->persist($district);
            $entityManager->flush();
            return $this->redirectToRoute('admin_districts');
        }
        return $this->render('@App/admin/district/edit_district.html.twig', array(
            'district'        => $district,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/district/delete/{slug}", name="admin_district_delete")
     * @Method("DELETE")
     * @ParamConverter("district", options={"mapping": {"slug": "slug"}})
     */
    public function DistrictDeleteAction(Request $request, District $district)
    {
        $form = $this->createDeleteForm($district);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($district);
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_districts');
    }

    private function createDeleteForm(District $district)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_district_delete', array('slug' => $district->getSlug())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
