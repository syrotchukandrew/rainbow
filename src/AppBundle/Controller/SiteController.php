<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 17.02.16
 * Time: 9:30
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Estate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class SiteController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $estates = $em->getRepository('AppBundle:Estate')->getEstateExclusiveWithFiles();
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

        return $this->render("AppBundle::site/index.html.twig", array('estates' => $estates));
    }

    /**
     * @Route("/show_estate/{slug}", name="show_estate")
     */
    public function showEstateAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $estate = $em->getRepository('AppBundle\Entity\Estate')->getEstateWithDistrictComment($slug);
//       dump($estate);
//        return new Response('ok');
        return $this->render('AppBundle:site:show_estate.html.twig', array('estate' => $estate[0]));
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