<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 18.03.16
 * Time: 11:34
 */

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("has_role('ROLE_MANAGER')")
 * @Route("/admin")
 */
class AdminMenuItemController extends Controller
{
    /**
     * @Route("/items", name="admin_items")
     */
    public function showItemsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $items = $em->getRepository('AppBundle:MenuItem')->findAll();
        $this->render('@App/admin/menu_item/items.html.twig', array('items' => $items));
    }
}