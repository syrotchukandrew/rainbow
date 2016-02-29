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
        $em = $this->getDoctrine()->getManager();
        $estates = $em->getRepository('AppBundle:Estate')->findBy(array('exclusive' => true));
        return $this->render("AppBundle::site/index.html.twig", array('estates' => $estates));
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
     * @Route("/show_category/{slug}", name="show_category")
     */
    public function showCategoryAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $estates = $em->getRepository('AppBundle\Entity\Estate')->getEstateFromCategory($slug);
//        dump($estates);
//        return new Response('ok');
        return $this->render("AppBundle::site/index.html.twig", array('estates' => $estates));
    }

    /**
     * @Route("/show_rent/{slug}", name="show_rent")
     */
    public function showRentAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $estates = $em->getRepository('AppBundle\Entity\Estate')->getEstateForRent($slug);
        dump($estates);
        return new Response('ok');
    }

    /**
     * @Route("/slideshow", name="slideshow")
     */
    public function gallerySlideAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $estate = $em->getRepository('AppBundle:Estate')->findOneBy(array('id' => 1));
        return $this->render("AppBundle::slideshow.html.twig", array('estate' => $estate));

    }

}