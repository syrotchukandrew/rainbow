<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Category;
use AppBundle\Form\CategoryType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
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
    public function categoriesAction(Request $request)
    {
        $entityManager = $this->doctrine->getManager();
        $repo = $entityManager->getRepository(\AppBundle\Entity\Category::class);
        $categories = $repo->childrenHierarchy();
        return $this->render("@App/admin/category/categories.html.twig", ['categories' => $categories]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/category_root/new', name: 'admin_category_root_new', methods: ['GET', 'POST'])]
    public function newCategoryRootAction(Request $request)
    {
        $entityManager = $this->doctrine->getManager();
        $repo = $entityManager->getRepository(\AppBundle\Entity\Category::class);
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
        return $this->render('@App/admin/category/new_category_root.html.twig', array(
            'category' => $category,
            'form' => $form->createView(),
        ));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/category/new', name: 'admin_category_new', methods: ['GET', 'POST'])]
    public function newCategoryAction(Request $request)
    {
        $entityManager = $this->doctrine->getManager();
        $repo = $entityManager->getRepository(\AppBundle\Entity\Category::class);
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
        return $this->render('@App/admin/category/new_category.html.twig', array(
            'category' => $category,
            'form' => $form->createView(),
        ));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/category/edit/{slug}', name: 'admin_category_edit', methods: ['GET', 'POST'])]
    public function categoryEditAction(#[MapEntity(mapping: ['slug' => 'slug'])] Category $category, Request $request)
    {
        $entityManager = $this->doctrine->getManager();
        $repo = $entityManager->getRepository(\AppBundle\Entity\Category::class);
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
        return $this->render('@App/admin/category/edit_category.html.twig', array(
            'category'        => $category,
            'edit_form'   => $editForm->createView(),
        ));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/category/delete/{slug}', name: 'admin_category_delete', methods: ['GET', 'DELETE'])]
    public function categoryDeleteAction(Request $request, #[MapEntity(mapping: ['slug' => 'slug'])] Category $category)
    {
        $entityManager = $this->doctrine->getManager();
        $repo = $entityManager->getRepository(\AppBundle\Entity\Category::class);
        $deleteForm = $this->createForm(CategoryType::class, $category, ['method' => 'DELETE']);
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $entityManager->remove($category);
            $repo->verify();
            $repo->recover();
            $entityManager->flush();
            return $this->redirectToRoute('admin_categories');
        }
        return $this->render('@App/admin/category/delete_category.html.twig', array(
            'category'        => $category,
            'delete_form'   => $deleteForm->createView(),
        ));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/category/up/{slug}', name: 'admin_category_up', methods: ['GET', 'POST'])]
    public function categoryUpAction(Request $request, #[MapEntity(mapping: ['slug' => 'slug'])] Category $category)
    {
        $entityManager = $this->doctrine->getManager();
        $repo = $entityManager->getRepository(\AppBundle\Entity\Category::class);
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
    public function categoryDownAction(Request $request, #[MapEntity(mapping: ['slug' => 'slug'])] Category $category)
    {
        $entityManager = $this->doctrine->getManager();
        $repo = $entityManager->getRepository(\AppBundle\Entity\Category::class);
        if ($category->getParent()) {
            $repo->moveDown($category);
            $repo->verify();
            $repo->recover();
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_categories');
    }
}
