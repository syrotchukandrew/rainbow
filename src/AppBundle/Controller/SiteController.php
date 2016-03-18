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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem("Homepage");
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
     * @ParamConverter("category", class="AppBundle\Entity\Category", options={"mapping": {"slug": "title"}})
     */
    public function showCategoryAction(Request $request, Category $category)
    {
        $em = $this->getDoctrine()->getManager();
        $estates = $em->getRepository('AppBundle\Entity\Estate')->getEstateFromCategory($category->getTitle());
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $node = $category;
        while ($node) {
            $breadcrumbs->prependItem($node->getTitle());
            $node = $node->getParent();
        }
        $breadcrumbs->prependItem("Homepage");

        return $this->render("AppBundle::site/index.html.twig", array('estates' => $estates));
    }

    /**
     * @Route("/show_estate/{slug}", name="show_estate")
     */
    public function showEstateAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $estate = $em->getRepository('AppBundle\Entity\Estate')->getEstateWithDistrictComment($slug);
        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $node = $estate->getCategory();
        $breadcrumbs->prependItem($estate->getTitle());
        while ($node) {
            $breadcrumbs->prependItem($node->getTitle());
            $node = $node->getParent();
        }
        $breadcrumbs->prependItem("Homepage");
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment, [
            'method' => 'POST',
        ])
            ->add('addComment', SubmitType::class, ['label' => 'common.save',
                'attr' => ['class' => 'btn btn-primary']
            ]);
            $commentForm->handleRequest($request);
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $comment->setEstate($estate);
                if ($this->getUser()->hasRole('ROLE_ADMIN')) {
                    $comment->setEnabled(true);
                } else {
                    $comment->setEnabled(false);
                }
                $em = $this->getDoctrine()->getManager();
                $em->persist($comment);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'site.flush_comment');
                return $this->redirectToRoute('show_estate', array('slug' => $estate->getSlug()));
            }
        return $this->render('AppBundle:site:show_estate.html.twig', array('estate' => $estate,
            'commentForm' => $commentForm->createView()));
    }

    /**
     * @Route("/search", name="site_search")
     * */
    public function searchAction(Request $request)
    {
        $finalCategories = $this->container->get('app.final_category_finder')->findFinalCategories();
        $searchForm = $this->createForm(SearchType::class, null, array(
            'action' => $this->generateUrl('site_search'),
            'categories_choices' => $finalCategories
        ))
            ->add('search', SubmitType::class, ['label' => 'Искать',
                'attr' => ['class' => 'btn btn-default']
            ]);

        $searchForm->handleRequest($request);
        if ($searchForm->isValid() && $searchForm->isSubmitted()) {
            $estates = $this->get('app.search')->searchEstate($searchForm->getData());
            // return new Response();
            return $this->render('AppBundle:site:index.html.twig', array('estates' => $estates));
        }

        return $this->render('@App/site/search.html.twig', array(
            'form' => $searchForm->createView(),
        ));
    }

    /**
     * @Route(name="show_menu_item")
     *
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
}