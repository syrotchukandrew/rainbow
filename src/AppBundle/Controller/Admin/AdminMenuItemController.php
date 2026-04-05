<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 18.03.16
 * Time: 11:34
 */

declare(strict_types=1);

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\MenuItem;
use AppBundle\Form\MenuItemType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MANAGER')]
#[Route('/admin')]
class AdminMenuItemController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/menu_items', name: 'admin_items')]
    public function showItemsAction(Request $request): Response
    {
        return $this->render('admin/menu_item/items.html.twig');
    }

    #[Route('/menu_item/new', name: 'admin_add_menu_item')]
    public function addItemAction(Request $request): Response
    {
        $em = $this->doctrine->getManager();
        $menuItem = new MenuItem();
        $form = $this->createForm(MenuItemType::class, $menuItem);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($menuItem);
            $em->flush();
            return $this->redirectToRoute('admin_items');
        }
        return $this->render('admin/menu_item/new_item.html.twig', array(
            'item' => $menuItem,
            'form' => $form->createView(),
        ));
    }

    #[Route('/menu_item/show/{id}', name: 'admin_item_show')]
    public function showItemAction(Request $request, MenuItem $menuItem): Response
    {
        $form = $this->deleteForm($menuItem);

        return $this->render('admin/menu_item/show_item.html.twig',
            array('item' => $menuItem, 'delete_form' => $form->createView()));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/menu_item/edit/{id}', name: 'admin_item_edit', methods: ['GET', 'PUT'])]
    public function editItemAction(Request $request, MenuItem $menuItem): Response
    {
        $em = $this->doctrine->getManager();
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

        return $this->render('admin/menu_item/edit_menu_item.html.twig',
            array('form' => $form->createView(),
            ));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/menu_item/delete/{id}', name: 'admin_menu_item_delete', methods: ['DELETE'])]
    public function deleteCommentAction(Request $request, MenuItem $menuItem): Response
    {
        $form = $this->deleteForm($menuItem);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->doctrine->getManager();
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
