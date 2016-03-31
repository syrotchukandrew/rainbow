<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 17.02.16
 * Time: 9:30
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\MenuItem;
use AppBundle\Entity\Estate;
use AppBundle\Entity\User;
use AppBundle\Entity\Category;
use AppBundle\Form\CommentType;
use AppBundle\Form\SearchType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $estates,
            $request->query->getInt('page', 1),
            5
        );
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("site.main");
        return $this->render("AppBundle::site/index.html.twig", array('pagination' => $pagination));
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
     * @ParamConverter("category", class="AppBundle\Entity\Category", options={"mapping": {"slug": "title"}})
     */
    public function showCategoryAction(Request $request, Category $category)
    {
        $em = $this->getDoctrine()->getManager();
        $estates = $em->getRepository('AppBundle\Entity\Estate')->getEstateFromCategory($category->getTitle());
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $estates,
            $request->query->getInt('page', 1),
            5
        );
        $this->container->get('app.breadcrumps_maker')->makeBreadcrumps($category);

        return $this->render("AppBundle::site/index.html.twig", array('pagination' => $pagination));
    }

    /**
     * @Route("/show_estate/{slug}", name="show_estate", options={"expose"=true})
     */
    public function showEstateAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $estate = $em->getRepository('AppBundle\Entity\Estate')->getEstateWithDistrictComment($slug);
        $this->container->get('app.breadcrumps_maker')->makeBreadcrumps($estate->getCategory(), $estate);

        return $this->render('AppBundle:site:show_estate.html.twig', array('estate' => $estate));
    }

    /**
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route("/comment/{slug}/new", name = "comment_new")
     * @Method("POST")estates
     * @ParamConverter("estate", options={"mapping": {"slug": "slug"}})
     */
    public function commentNewAction(Estate $estate, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->getUser()->hasRole('ROLE_ADMIN')) {
                $comment->setEnabled(true);
            } else {
                $comment->setEnabled(false);
            }
            $comment->setEstate($estate);
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->get('session')->getFlashBag()->add('success', 'site.flush_comment');
            return $this->redirectToRoute('show_estate', array('slug' => $estate->getSlug()));
        }

        return $this->render('@App/site/_comment_form.html.twig', array(
            'estate' => $estate,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/search", name="site_search")
     * */
    public function searchAction(Request $request)
    {
        $finalCategories = $this->container->get('app.final_category_finder')->findFinalCategories();
        $searchForm = $this->createForm(SearchType::class, null, array(
            'action' => $this->generateUrl('site_search_result'),
            'categories_choices' => $finalCategories));

        return $this->render('@App/site/search.html.twig', array(
            'form' => $searchForm->createView(),
        ));
    }

    /**
     * @Route("/search/result", name="site_search_result")
     * */
    public function searchResultAction(Request $request)
    {
        $finalCategories = $this->container->get('app.final_category_finder')->findFinalCategories();
        $searchForm = $this->createForm(SearchType::class, null, array(
            'action' => $this->generateUrl('site_search_result'),
            'categories_choices' => $finalCategories));

        $searchForm->handleRequest($request);
        if ($searchForm->isValid() && $searchForm->isSubmitted()) {
            $estates = $this->get('app.search')->searchEstate($searchForm->getData());
            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $estates,
                $request->query->getInt('page', 1),
                5
            );
            return $this->render('AppBundle:site:index.html.twig', array('pagination' => $pagination));
        }

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/menu_item", name="show_menu_item")
     */
    public function showMenuItemAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $menuitems = $em->getRepository('AppBundle:MenuItem')->findAll();
        return $this->render('AppBundle:includes:menu_items.html.twig', array('items' => $menuitems));
    }

    /**
     * @Route("/description_menu/{id}", name="show_description_menu_item")
     * @ParamConverter("MenuItem", options={"mapping": {"id": "id"}})
     */
    public function showDescriptionMenuItem(Request $request, MenuItem $menuItem)
    {
        return $this->render('AppBundle:site:show_description_menu_item.html.twig', array('item' => $menuItem));
    }

    /**
     * @Route("/add_favorites/{estate}/{user}", name = "add_estate_to_favorites")
     * @ParamConverter("estate", class="AppBundle\Entity\Estate", options={"mapping": {"estate": "slug"}})
     * @ParamConverter("user", class="AppBundle\Entity\User", options={"mapping": {"user": "id"}})
     */
    public function addEstateToFavoritesAction(Estate $estate, User $user, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$user->hasEstate($estate)) {
            $user->addEstate($estate);
            $em->persist($user);
            $em->flush();
        }

        return $this->redirectToRoute('show_estate', array('slug' => $estate->getSlug()));
    }

    /**
     * @Route("/delete_favorites/{estate}/{user}", name = "delete_estate_from_favorites")
     * @ParamConverter("estate", class="AppBundle\Entity\Estate", options={"mapping": {"estate": "slug"}})
     * @ParamConverter("user", class="AppBundle\Entity\User", options={"mapping": {"user": "id"}})
     */
    public function deleteEstateFromFavoritesAction(Estate $estate, User $user, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if ($user->hasEstate($estate)) {
            $user->removeEstate($estate);
            $em->persist($user);
            $em->flush();
        }

        return $this->redirectToRoute('show_estate', array('slug' => $estate->getSlug()));
    }

    /**
     * @Route("/pdf/{estate}", name = "pdf_estate")
     * @ParamConverter("estate", class="AppBundle\Entity\Estate", options={"mapping": {"estate": "slug"}})
     */
    public function pdfEstateAction(Estate $estate, Request $request)
    {
        $html = $this->renderView('@App/site/pdf.html.twig', array('estate' => $estate));

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('images' => true)), 200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="file.pdf"'
            )
        );
    }

    /**
     * @Route("/livesearch", name="livesearch", options={"expose"=true})
     */
    public function livesearchAction(Request $request)
    {
        if ($request->getMethod() === 'GET') {
            return new Response(json_encode($this->get('app.searcher')->search()));
        }
        if ($request->getMethod() === 'POST') {
            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $this->get('app.searcher')->search(),
                $request->query->getInt('page', 1),
                10
            );
            return $this->render('@App/site/index.html.twig', array('pagination' => $pagination));
        }
    }
}