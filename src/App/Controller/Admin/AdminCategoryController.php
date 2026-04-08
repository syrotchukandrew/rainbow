<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
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
class AdminCategoryController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/categories', name: 'admin_categories', methods: ['GET'])]
    public function categoriesAction(): Response
    {
        return $this->render('admin/category/categories.html.twig');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/category_root/new', name: 'admin_category_root_new', methods: ['GET', 'POST'])]
    public function newCategoryRootAction(Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repo = $entityManager->getRepository(\App\Entity\Category::class);
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category->setParent(null);
            $entityManager->persist($category);
            $repo->verify();
            $repo->recover();
            $entityManager->flush();
            return $this->redirectToRoute('admin_categories');
        }
        return $this->render('admin/category/new_category_root.html.twig', array(
            'category' => $category,
            'form' => $form->createView(),
        ));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/category/new', name: 'admin_category_new', methods: ['GET', 'POST'])]
    public function newCategoryAction(Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repo = $entityManager->getRepository(\App\Entity\Category::class);
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category, array('isForm_cat' => true));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $parent = $form['parent']->getData();
            $category->setParent($parent);
            $parent->addChild($category);
            $entityManager->persist($category);
            $repo->verify();
            $repo->recover();
            $entityManager->flush();
            return $this->redirectToRoute('admin_categories');
        }
        return $this->render('admin/category/new_category.html.twig', array(
            'category' => $category,
            'form' => $form->createView(),
        ));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/category/edit/{slug}', name: 'admin_category_edit', methods: ['GET', 'POST'])]
    public function categoryEditAction(#[MapEntity(mapping: ['slug' => 'slug'])] Category $category, Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repo = $entityManager->getRepository(\App\Entity\Category::class);
        $editForm = $this->createForm(CategoryType::class, $category, array('isForm_cat' => true));
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $parent = $editForm['parent']->getData();
            $category->setParent($parent);
            $entityManager->persist($category);
            $repo->verify();
            $repo->recover();
            $entityManager->flush();
            return $this->redirectToRoute('admin_categories');
        }
        return $this->render('admin/category/edit_category.html.twig', array(
            'category'        => $category,
            'edit_form'   => $editForm->createView(),
        ));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/category/delete/{slug}', name: 'admin_category_delete', methods: ['GET', 'DELETE'])]
    public function categoryDeleteAction(Request $request, #[MapEntity(mapping: ['slug' => 'slug'])] Category $category): Response
    {
        $entityManager = $this->doctrine->getManager();
        $repo = $entityManager->getRepository(\App\Entity\Category::class);
        $deleteForm = $this->createForm(CategoryType::class, $category, ['method' => 'DELETE']);
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $entityManager->remove($category);
            $repo->verify();
            $repo->recover();
            $entityManager->flush();
            return $this->redirectToRoute('admin_categories');
        }
        return $this->render('admin/category/delete_category.html.twig', array(
            'category'        => $category,
            'delete_form'   => $deleteForm->createView(),
        ));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/category/up/{slug}', name: 'admin_category_up', methods: ['GET', 'POST'])]
    public function categoryUpAction(Request $request, #[MapEntity(mapping: ['slug' => 'slug'])] Category $category): RedirectResponse
    {
        $entityManager = $this->doctrine->getManager();
        $repo = $entityManager->getRepository(\App\Entity\Category::class);
        if ($category->getParent()) {
            $repo->moveUp($category);
            $repo->verify();
            $repo->recover();
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_categories');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/category/down/{slug}', name: 'admin_category_down', methods: ['GET'])]
    public function categoryDownAction(Request $request, #[MapEntity(mapping: ['slug' => 'slug'])] Category $category): RedirectResponse
    {
        $entityManager = $this->doctrine->getManager();
        $repo = $entityManager->getRepository(\App\Entity\Category::class);
        if ($category->getParent()) {
            $repo->moveDown($category);
            $repo->verify();
            $repo->recover();
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_categories');
    }
}
