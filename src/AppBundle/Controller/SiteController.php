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
//        $em = $this->getDoctrine()->getManager();
//        $rootMenuItems = $em->getRepository("AppBundle:Category")->findBy(['parent' => null]);
//
//
//        $collection = new ArrayCollection($rootMenuItems);
//        $recursivemenuIterator = new RecursiveMenuItemIterator($collection);
//
//        $iterator = new \RecursiveIteratorIterator($recursivemenuIterator, \RecursiveIteratorIterator::SELF_FIRST);

//        dump($iterator);
//        return new Response('ok');
        //return $this->render("AppBundle::menu.html.twig", ['menuitems' => $iterator]);
       // return $this->render("AppBundle::menu.html.twig", ['links' => $iterator]);


        // ... your code before
      $em = $this->getDoctrine()->getManager();
//        $cat1= new CategoryEntity();
//        $cat1->setTitle('Фрукты');
//
//        $subcat = new CategoryEntity();
//        $subcat->setTitle('Экзотические');
//        $subcat->setParent($cat1);
//
//        $cat2 = new CategoryEntity();
//        $cat2->setTitle('Овощи');
//
//        $em->persist($cat1);
//        $em->persist($cat2);
//        $em->persist($subcat);
//        $em->flush();

        $categoryEntity = $em->getRepository('AppBundle\Entity\Cat');
        $categories = $categoryEntity->childrenHierarchy();

//        dump($categories);
//        return new Response('ok');
        return $this->render("AppBundle::menu.html.twig", ['links' => $categories]);


    }

}