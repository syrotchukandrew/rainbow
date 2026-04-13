<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\District;
use App\Form\DistrictType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_MANAGER')]
#[Route('/admin')]
class AdminDistrictController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/districts', name: 'admin_districts', methods: ['GET'])]
    public function districtsAction(Request $request): Response
    {
        return $this->render('admin/district/districts.html.twig');
    }

    #[Route('/district/show/{slug}', name: 'admin_district_show', methods: ['GET'])]
    public function districtShowAction(#[MapEntity(mapping: ['slug' => 'slug'])] District $district, Request $request): Response
    {
        $deleteForm = $this->createDeleteForm($district);
        return $this->render('admin/district/show_district.html.twig', array(
            'district'        => $district,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/district/new', name: 'admin_district_new', methods: ['GET', 'POST'])]
    public function newDistrictAction(Request $request): Response
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
        return $this->render('admin/district/new_district.html.twig', array(
            'district' => $district,
            'form' => $form->createView(),
        ));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/district/edit/{slug}', name: 'admin_district_edit', methods: ['GET', 'POST'])]
    public function districtEditAction(#[MapEntity(mapping: ['slug' => 'slug'])] District $district, Request $request): Response
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
        return $this->render('admin/district/edit_district.html.twig', array(
            'district'        => $district,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/district/delete/{slug}', name: 'admin_district_delete', methods: ['DELETE'])]
    public function districtDeleteAction(Request $request, #[MapEntity(mapping: ['slug' => 'slug'])] District $district): RedirectResponse
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
