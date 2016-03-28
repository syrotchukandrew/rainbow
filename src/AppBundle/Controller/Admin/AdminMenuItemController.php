<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 18.03.16
 * Time: 11:34
 */

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\MenuItem;
use AppBundle\Form\MenuItemType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("has_role('ROLE_MANAGER')")
 * @Route("/admin")
 */
class AdminMenuItemController extends Controller
{
    /**
     * @Route("/menu_items", name="admin_items")
     */
    public function showItemsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $items = $em->getRepository('AppBundle:MenuItem')->findAll();
        return $this->render('@App/admin/menu_item/items.html.twig', array('items' => $items));
    }

    /**
     * @Route("/menu_item/new", name="admin_add_menu_item")
     */
    public function addItemAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $menu_item = new MenuItem();
        $form = $this->createForm(MenuItemType::class, $menu_item);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($menu_item);
            $em->flush();
            return $this->redirectToRoute('admin_items');
        }
        return $this->render('@App/admin/menu_item/new_item.html.twig', array(
            'item' => $menu_item,
            'form' => $form->createView(),
        ));

    }

    /**
     * @Route("/menu_item/show/{id}", name="admin_item_show")
     * @ParamConverter("MenuItem", options={"mapping": {"id": "id"}})
     */
    public function showItemAction(Request $request, MenuItem $menuItem)
    {
        //for delete
        $form = $this->deleteForm($menuItem);

        return $this->render('@App/admin/menu_item/show_item.html.twig',
            array('item' => $menuItem, 'delete_form' => $form->createView()));
    }

    /**
     * @Route("/menu_item/edit/{id}", name="admin_item_edit")
     * @Method({"GET", "PUT"})
     * @Security("has_role('ROLE_ADMIN')")
     * @ParamConverter("MenuItem", options={"mapping": {"id": "id"}})
     */
    public function editItemAction(Request $request, MenuItem $menuItem)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(MenuItemType::class, $menuItem, [
            'method' => 'PUT',
            'attr' => ['class' => 'horizontal']
        ])
            ->add('submit', SubmitType::class, ['label' => 'Edit',
                'attr' => ['class' => 'btn btn-raised btn-default']
            ]);
        if ($request->getMethod() == 'PUT') {
            $form->handleRequest($request);
            if ($form->isValid() && $form->isSubmitted()) {
                $em->persist($menuItem);
                $em->flush();
                return $this->redirectToRoute('admin_items');
            }
        }

        return $this->render('@App/admin/menu_item/edit_menu_item.html.twig',
            array('form' => $form->createView(),
            ));
    }

    /**
     * @Route("/menu_item/delete/{id}", name="admin_menu_item_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN')")
     * @ParamConverter("comment", options={"mapping": {"id": "id"}})
     */
    public function deleteCommentAction(Request $request, MenuItem $menuItem)
    {
        $form = $this->deleteForm($menuItem);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($menuItem);
            $entityManager->flush();
        }
        return $this->redirectToRoute('admin_items');
    }

    private function deleteForm(MenuItem $menuItem)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_menu_item_delete',
                array('id' => $menuItem->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}