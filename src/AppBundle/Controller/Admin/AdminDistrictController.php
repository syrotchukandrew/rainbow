<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\District;
use AppBundle\Form\DistrictType;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Security("is_granted('ROLE_MANAGER')")
 * @Route("/admin")
 */
class AdminDistrictController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/districts", name="admin_districts", methods={"GET"})     */
    public function districtsAction(Request $request)
    {
        $districts = $this->doctrine->getRepository(\AppBundle\Entity\District::class)->findAll();
        return $this->render('@App/admin/district/districts.html.twig', array('districts' => $districts));
    }

    /**
     * @Route("/district/show/{slug}", name="admin_district_show", methods={"GET"})     * @ParamConverter("district", options={"mapping": {"slug": "slug"}})
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
     * @Route("/district/new", name="admin_district_new", methods={"GET", "POST"})     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function newDistrictAction(Request $request)
    {
        $entityManager = $this->doctrine->getManager();
        $district = new District();
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
     * @Route("/district/edit/{slug}", name="admin_district_edit", methods={"GET", "POST"})     * @Security("is_granted('ROLE_ADMIN')")
     * @ParamConverter("district", options={"mapping": {"slug": "slug"}})
     */
    public function districtEditAction(District $district, Request $request)
    {
        $entityManager = $this->doctrine->getManager();
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
     * @Route("/district/delete/{slug}", name="admin_district_delete", methods={"DELETE"})     * @Security("is_granted('ROLE_ADMIN')")
     * @ParamConverter("district", options={"mapping": {"slug": "slug"}})
     */
    public function DistrictDeleteAction(Request $request, District $district)
    {
        $form = $this->createDeleteForm($district);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->doctrine->getManager();

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
