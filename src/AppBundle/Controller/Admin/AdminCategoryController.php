<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Category;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Form\CategoryType;



/**
 * @Security("has_role('ROLE_MANAGER')")
 * @Route("/admin")
 */
class AdminCategoryController extends Controller
{
    /**
     * @Route("/categories", name="admin_categories")
     * @Method("GET")
     */
    public function categoriesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager()->getRepository('AppBundle\Entity\Category');
        $categories = $em->childrenHierarchy();
        return $this->render("@App/admin/category/categories.html.twig", ['categories' => $categories]);
    }

    /**
     * @Route("/category_root/new", name="admin_category_root_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newCategoryRootAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category->setParent(null);
            $entityManager->persist($category);
            $entityManager->flush();
            return $this->redirectToRoute('admin_categories');
        }
        return $this->render('@App/admin/category/new_category_root.html.twig', array(
            'category' => $category,
            'form' => $form->createView(),
        ));
    }




    private function createDeleteForm(Category $category)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_district_delete', array('slug' => $category->getSlug())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
