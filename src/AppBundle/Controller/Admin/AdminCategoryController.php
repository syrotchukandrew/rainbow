<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AppController;
use AppBundle\Entity\Category;
use AppBundle\Form\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("is_granted('ROLE_MANAGER')")
 * @Route("/admin")
 */
class AdminCategoryController extends AppController
{
    /**
     * @Route("/categories", name="admin_categories", methods={"GET"})     */
    public function categoriesAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repo = $entityManager->getRepository(\AppBundle\Entity\Category::class);
        $categories = $repo->childrenHierarchy();
        return $this->render("@App/admin/category/categories.html.twig", ['categories' => $categories]);
    }

    /**
     * @Route("/category_root/new", name="admin_category_root_new", methods={"GET", "POST"})     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function newCategoryRootAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
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

    /**
     * @Route("/category/new", name="admin_category_new", methods={"GET", "POST"})     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function newCategoryAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
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

    /**
     * @Route("/category/edit/{slug}", name="admin_category_edit", methods={"GET", "POST"})     * @Security("is_granted('ROLE_ADMIN')")
     * @ParamConverter("category", options={"mapping": {"slug": "slug"}})
     */
    public function categoryEditAction(Category $category, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
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

    /**
     * @Route("/category/delete/{slug}", name="admin_category_delete", methods={"GET", "DELETE"})     * @Security("is_granted('ROLE_ADMIN')")
     * @ParamConverter("estate", options={"mapping": {"slug": "slug"}})
     */
    public function categoryDeleteAction(Request $request, Category $category)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repo = $entityManager->getRepository(\AppBundle\Entity\Category::class);
        $deleteForm = $this->createForm(CategoryType::class, $category,['method' => 'DELETE']);
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

    /**
     * @Route("/category/up/{slug}", name="admin_category_up", methods={"GET", "POST"})     * @Security("is_granted('ROLE_ADMIN')")
     * @ParamConverter("category", options={"mapping": {"slug": "slug"}})
     */
    public function categoryUpAction(Request $request, Category $category)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repo = $entityManager->getRepository(\AppBundle\Entity\Category::class);
        if ($category->getParent()){
            $repo->moveUp($category);
            $repo->verify();
            $repo->recover();
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_categories');
    }

    /**
     * @Route("/category/down/{slug}", name="admin_category_down", methods={"GET"})     * @Security("is_granted('ROLE_ADMIN')")
     * @ParamConverter("category", options={"mapping": {"slug": "slug"}})
     */
    public function categoryDownAction(Request $request, Category $category)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repo = $entityManager->getRepository(\AppBundle\Entity\Category::class);
        if ($category->getParent()){
            $repo->moveDown($category);
            $repo->verify();
            $repo->recover();
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_categories');
    }
}
