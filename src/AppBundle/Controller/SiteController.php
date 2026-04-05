<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 17.02.16
 * Time: 9:30
 */

declare(strict_types=1);

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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;


class SiteController extends AbstractController
{
    private const ITEMS_PER_PAGE = 5;
    private const LIVESEARCH_ITEMS_PER_PAGE = 10;

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

    #[Route('/', name: 'homepage', methods: ['GET'])]
    public function indexAction(): Response
    {
        $this->breadcrumbs->addItem("site.main");
        return $this->render("site/index.html.twig", ['apiUrl' => '/api/public/estates']);
    }

    #[Route('/menu', name: 'menu', methods: ['GET'])]
    public function menuAction(Request $request): Response
    {
        $em = $this->doctrine->getManager();
        $categoryEntity = $em->getRepository(\AppBundle\Entity\Category::class);
        $categories = $categoryEntity->childrenHierarchy();

        return $this->render("includes/menu.html.twig", ['links' => $categories]);
    }

    #[Route('/show_category/{slug}', name: 'show_category', methods: ['GET'])]
    public function showCategoryAction(Request $request, #[MapEntity(mapping: ['slug' => 'title'])] Category $category): Response
    {
        $this->breadcrumpsMaker->makeBreadcrumps($category);
        return $this->render("site/index.html.twig", [
            'apiUrl' => '/api/public/estates?category=' . $category->getSlug(),
        ]);
    }

    #[Route('/show_estate/{slug}', name: 'show_estate', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function showEstateAction(Request $request, $slug): Response
    {
        $em = $this->doctrine->getManager();
        $estate = $em->getRepository(\AppBundle\Entity\Estate::class)->getEstateWithDistrictComment($slug);
        $this->breadcrumpsMaker->makeBreadcrumps($estate->getCategory(), $estate);

        return $this->render('site/show_estate.html.twig', array('estate' => $estate));
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/comment/{slug}/new', name: 'comment_new', methods: ['POST'])]
    public function commentNewAction(#[MapEntity(mapping: ['slug' => 'slug'])] Estate $estate, Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $currentUser = $this->getUser();
            if ($currentUser !== null && $currentUser->hasRole('ROLE_ADMIN')) {
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

        return $this->render('site/_comment_form.html.twig', array(
            'estate' => $estate,
            'form' => $form->createView(),
        ));
    }

    #[Route('/search', name: 'site_search', methods: ['GET', 'POST'])]
    public function searchAction(Request $request): Response
    {
        $finalCategories = $this->finalCategoryFinder->findFinalCategories();
        $searchForm = $this->createForm(SearchType::class, null, array(
            'action' => $this->generateUrl('site_search_result'),
            'categories_choices' => $finalCategories));

        return $this->render('site/search.html.twig', array(
            'form' => $searchForm->createView(),
        ));
    }

    #[Route('/search/result', name: 'site_search_result', methods: ['GET', 'POST'])]
    public function searchResultAction(Request $request): Response
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
                self::ITEMS_PER_PAGE
            );
            return $this->render('site/index.html.twig', array('pagination' => $pagination));
        }

        return $this->redirectToRoute('homepage');
    }

    #[Route('/menu_item', name: 'show_menu_item', methods: ['GET'])]
    public function showMenuItemAction(Request $request): Response
    {
        $em = $this->doctrine->getManager();
        $menuitems = $em->getRepository(\AppBundle\Entity\MenuItem::class)->findAll();
        return $this->render('includes/menu_items.html.twig', array('items' => $menuitems));
    }

    #[Route('/description_menu/{id}', name: 'show_description_menu_item', methods: ['GET'])]
    public function showDescriptionMenuItem(Request $request, MenuItem $menuItem): Response
    {
        return $this->render('site/show_description_menu_item.html.twig', array('item' => $menuItem));
    }

    #[Route('/add_favorites/{estate}/{user}', name: 'add_estate_to_favorites', methods: ['GET', 'POST'])]
    public function addEstateToFavoritesAction(
        #[MapEntity(mapping: ['estate' => 'slug'])] Estate $estate,
        #[MapEntity(mapping: ['user' => 'id'])] User $user,
        Request $request
    ): RedirectResponse {
        $em = $this->doctrine->getManager();
        if (!$user->hasEstate($estate)) {
            $user->addEstate($estate);
            $em->persist($user);
            $em->flush();
        }

        return $this->redirectToRoute('show_estate', array('slug' => $estate->getSlug()));
    }

    #[Route('/delete_favorites/{estate}/{user}', name: 'delete_estate_from_favorites', methods: ['GET', 'POST'])]
    public function deleteEstateFromFavoritesAction(
        #[MapEntity(mapping: ['estate' => 'slug'])] Estate $estate,
        #[MapEntity(mapping: ['user' => 'id'])] User $user,
        Request $request
    ): RedirectResponse {
        $em = $this->doctrine->getManager();
        if ($user->hasEstate($estate)) {
            $user->removeEstate($estate);
            $em->persist($user);
            $em->flush();
        }

        return $this->redirectToRoute('show_estate', array('slug' => $estate->getSlug()));
    }

    #[Route('/pdf/{estate}', name: 'pdf_estate', methods: ['GET'])]
    public function pdfEstateAction(#[MapEntity(mapping: ['estate' => 'slug'])] Estate $estate, Request $request): Response
    {
        $html = $this->renderView('site/pdf.html.twig', array('estate' => $estate));

        return new Response(
            $this->pdf->getOutputFromHtml($html, array('images' => true)), 200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="file.pdf"'
            )
        );
    }

    #[Route('/livesearch', name: 'livesearch', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function livesearchAction(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return new Response(json_encode($this->searcher->search()));
        }
        if ($request->isMethod('POST')) {
            $pagination = $this->paginator->paginate(
                $this->searcher->search(),
                $request->query->getInt('page', 1),
                self::LIVESEARCH_ITEMS_PER_PAGE
            );
            return $this->render('site/index.html.twig', array('pagination' => $pagination));
        }
        return new Response('', 405);
    }
}
