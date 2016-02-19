<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 17.02.16
 * Time: 9:30
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Estate\Estate;
use AppBundle\Entity\Estate\Flat;
use AppBundle\Entity\Estate\House;
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
     * @Route("/inherity", name="inherity")
     */
    public function inherityAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cat1 = new Estate();
        $cat1->setName('estate');
        $cat1->setUrl('dgdfgfgfds@grg.dsd');

        $subcat = new House();
        $subcat->setName('house');
        $subcat->setUrl('ladfk@jjj.jjj');
        $subcat->setPrice(452155.25);

        $cat2 = new Flat();
        $cat2->setName('flat');
        $cat2->setUrl('l222fk@jjj.jjj');
        $cat2->setVolume(47458);

        $em->persist($cat1);
        $em->persist($cat2);
        $em->persist($subcat);
        $em->flush();

        $categoryEntity = $em->getRepository('AppBundle\Entity\Estate\Flat')->findAll();


//        dump($categories);
//        return new Response('ok');
        return $this->render("AppBundle::inherity.html.twig", ['entity' => $categoryEntity]);


    }

}