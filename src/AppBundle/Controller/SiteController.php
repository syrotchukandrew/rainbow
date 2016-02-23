<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 17.02.16
 * Time: 9:30
 */

namespace AppBundle\Controller;


use AppBundle\MenuItem\RecursiveMenuItemIterator;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Cat as CategoryEntity;

class SiteController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render("AppBundle::site/index.html.twig");
    }

    /**
     * @Route("/menu", name="menu")
     */
    public function menuAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $categoryEntity = $em->getRepository('AppBundle\Entity\Category');
        $categories = $categoryEntity->childrenHierarchy();
        return $this->render("AppBundle::includes/menu.html.twig", ['links' => $categories]);
    }

    /**
     * @Route("/tree", name="tree")
     */
    public function treeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\Category');
        $query = $em
            ->createQueryBuilder()
            ->select('node')
            ->from('AppBundle\Entity\Category', 'node')
            ->orderBy('node.root, node.lft', 'ASC')
            ->where('node.root = 1')
            ->getQuery()
        ;
        $options = array('decorate' => true);
        $tree = $repo->buildTree($query->getArrayResult(), $options);
        return $this->render("AppBundle::tree.html.twig", ['tree' => $tree]);
    }

}