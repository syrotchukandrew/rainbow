<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 17.02.16
 * Time: 9:30
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Comment;
use AppBundle\Entity\Estate;
use AppBundle\Entity\MenuItem;
use AppBundle\Entity\User;
use AppBundle\Form\CommentType;
use AppBundle\Form\SearchType;
use AppBundle\Utils\BreadcrumpsMaker;
use AppBundle\Utils\FinalCategoryFinder;
use AppBundle\Utils\SearchManager;
use AppBundle\Utils\Searcher;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Snappy\Pdf;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;


class SiteController extends AbstractController
{
    private ManagerRegistry $doctrine;
    private PaginatorInterface $paginator;
    private BreadcrumpsMaker $breadcrumpsMaker;
    private FinalCategoryFinder $finalCategoryFinder;
    private SearchManager $searchManager;
    private Searcher $searcher;
    private Pdf $pdf;
    private Breadcrumbs $breadcrumbs;

    public function __construct(
        ManagerRegistry $doctrine,
        PaginatorInterface $paginator,
        BreadcrumpsMaker $breadcrumpsMaker,
        FinalCategoryFinder $finalCategoryFinder,
        SearchManager $searchManager,
        Searcher $searcher,
        Pdf $pdf,
        Breadcrumbs $breadcrumbs
    ) {
        $this->doctrine = $doctrine;
        $this->paginator = $paginator;
        $this->breadcrumpsMaker = $breadcrumpsMaker;
        $this->finalCategoryFinder = $finalCategoryFinder;
        $this->searchManager = $searchManager;
        $this->searcher = $searcher;
        $this->pdf = $pdf;
        $this->breadcrumbs = $breadcrumbs;
    }

    #[Route('/', name: 'homepage')]
    public function indexAction(Request $request)
    {
        $em = $this->doctrine->getManager();
        $estates = $em->getRepository(\AppBundle\Entity\Estate::class)->getEstateExclusiveWithFiles();
        $pagination = $this->paginator->paginate(
            $estates,
            $request->query->getInt('page', 1),
            5
        );
        $this->breadcrumbs->addItem("site.main");
        return $this->render("@App/site/index.html.twig", array('pagination' => $pagination));
    }

    #[Route('/menu', name: 'menu')]
    public function menuAction(Request $request)
    {
        $em = $this->doctrine->getManager();
        $categoryEntity = $em->getRepository(\AppBundle\Entity\Category::class);
        $categories = $categoryEntity->childrenHierarchy();

        return $this->render("@App/includes/menu.html.twig", ['links' => $categories]);
    }

    #[Route('/show_category/{slug}', name: 'show_category')]
    public function showCategoryAction(Request $request, #[MapEntity(mapping: ['slug' => 'title'])] Category $category)
    {
        $em = $this->doctrine->getManager();
        $estates = $em->getRepository(\AppBundle\Entity\Estate::class)->getEstateFromCategory($category->getTitle());
        $pagination = $this->paginator->paginate(
            $estates,
            $request->query->getInt('page', 1),
            5
        );
        $this->breadcrumpsMaker->makeBreadcrumps($category);

        return $this->render("@App/site/index.html.twig", array('pagination' => $pagination));
    }

    #[Route('/show_estate/{slug}', name: 'show_estate', options: ['expose' => true])]
    public function showEstateAction(Request $request, $slug)
    {
        $em = $this->doctrine->getManager();
        $estate = $em->getRepository(\AppBundle\Entity\Estate::class)->getEstateWithDistrictComment($slug);
        $this->breadcrumpsMaker->makeBreadcrumps($estate->getCategory(), $estate);

        return $this->render('@App/site/show_estate.html.twig', array('estate' => $estate));
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/comment/{slug}/new', name: 'comment_new', methods: ['POST'])]
    public function commentNewAction(#[MapEntity(mapping: ['slug' => 'slug'])] Estate $estate, Request $request)
    {
        $entityManager = $this->doctrine->getManager();
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
            $this->addFlash('success', 'site.flush_comment');
            return $this->redirectToRoute('show_estate', array('slug' => $estate->getSlug()));
        }

        return $this->render('@App/site/_comment_form.html.twig', array(
            'estate' => $estate,
            'form' => $form->createView(),
        ));
    }

    #[Route('/search', name: 'site_search')]
    public function searchAction(Request $request)
    {
        $finalCategories = $this->finalCategoryFinder->findFinalCategories();
        $searchForm = $this->createForm(SearchType::class, null, array(
            'action' => $this->generateUrl('site_search_result'),
            'categories_choices' => $finalCategories));

        return $this->render('@App/site/search.html.twig', array(
            'form' => $searchForm->createView(),
        ));
    }

    #[Route('/search/result', name: 'site_search_result')]
    public function searchResultAction(Request $request)
    {
        $finalCategories = $this->finalCategoryFinder->findFinalCategories();
        $searchForm = $this->createForm(SearchType::class, null, array(
            'action' => $this->generateUrl('site_search_result'),
            'categories_choices' => $finalCategories));

        $searchForm->handleRequest($request);
        if ($searchForm->isValid() && $searchForm->isSubmitted()) {
            $estates = $this->searchManager->searchEstate($searchForm->getData());
            $pagination = $this->paginator->paginate(
                $estates,
                $request->query->getInt('page', 1),
                5
            );
            return $this->render('@App/site/index.html.twig', array('pagination' => $pagination));
        }

        return $this->redirectToRoute('homepage');
    }

    #[Route('/menu_item', name: 'show_menu_item')]
    public function showMenuItemAction(Request $request)
    {
        $em = $this->doctrine->getManager();
        $menuitems = $em->getRepository(\AppBundle\Entity\MenuItem::class)->findAll();
        return $this->render('@App/includes/menu_items.html.twig', array('items' => $menuitems));
    }

    #[Route('/description_menu/{id}', name: 'show_description_menu_item')]
    public function showDescriptionMenuItem(Request $request, MenuItem $menuItem)
    {
        return $this->render('@App/site/show_description_menu_item.html.twig', array('item' => $menuItem));
    }

    #[Route('/add_favorites/{estate}/{user}', name: 'add_estate_to_favorites')]
    public function addEstateToFavoritesAction(
        #[MapEntity(mapping: ['estate' => 'slug'])] Estate $estate,
        #[MapEntity(mapping: ['user' => 'id'])] User $user,
        Request $request
    ) {
        $em = $this->doctrine->getManager();
        if (!$user->hasEstate($estate)) {
            $user->addEstate($estate);
            $em->persist($user);
            $em->flush();
        }

        return $this->redirectToRoute('show_estate', array('slug' => $estate->getSlug()));
    }

    #[Route('/delete_favorites/{estate}/{user}', name: 'delete_estate_from_favorites')]
    public function deleteEstateFromFavoritesAction(
        #[MapEntity(mapping: ['estate' => 'slug'])] Estate $estate,
        #[MapEntity(mapping: ['user' => 'id'])] User $user,
        Request $request
    ) {
        $em = $this->doctrine->getManager();
        if ($user->hasEstate($estate)) {
            $user->removeEstate($estate);
            $em->persist($user);
            $em->flush();
        }

        return $this->redirectToRoute('show_estate', array('slug' => $estate->getSlug()));
    }

    #[Route('/pdf/{estate}', name: 'pdf_estate')]
    public function pdfEstateAction(#[MapEntity(mapping: ['estate' => 'slug'])] Estate $estate, Request $request)
    {
        $html = $this->renderView('@App/site/pdf.html.twig', array('estate' => $estate));

        return new Response(
            $this->pdf->getOutputFromHtml($html, array('images' => true)), 200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="file.pdf"'
            )
        );
    }

    #[Route('/livesearch', name: 'livesearch', options: ['expose' => true])]
    public function livesearchAction(Request $request)
    {
        if ($request->getMethod() === 'GET') {
            return new Response(json_encode($this->searcher->search()));
        }
        if ($request->getMethod() === 'POST') {
            $pagination = $this->paginator->paginate(
                $this->searcher->search(),
                $request->query->getInt('page', 1),
                10
            );
            return $this->render('@App/site/index.html.twig', array('pagination' => $pagination));
        }
        return new Response('', 405);
    }
}
