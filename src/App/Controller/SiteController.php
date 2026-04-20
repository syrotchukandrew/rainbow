<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 17.02.16
 * Time: 9:30
 */

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\District;
use App\Entity\Estate;
use App\Entity\MenuItem;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\SearchType;
use App\Utils\BreadcrumbsMaker;
use App\Utils\FinalCategoryFinder;
use App\Utils\SearchManager;
use App\Utils\Searcher;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Knp\Snappy\Pdf;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Huluti\BreadcrumbsBundle\Model\Breadcrumbs;


class SiteController extends AbstractController
{
    private const int ITEMS_PER_PAGE = 5;
    private const int LIVESEARCH_ITEMS_PER_PAGE = 10;

    private ManagerRegistry $doctrine;
    private PaginatorInterface $paginator;
    private BreadcrumbsMaker $breadcrumbsMaker;
    private FinalCategoryFinder $finalCategoryFinder;
    private SearchManager $searchManager;
    private Searcher $searcher;
    private Pdf $pdf;
    private Breadcrumbs $breadcrumbs;
    private CacheInterface $cache;
    private RateLimiterFactory $livesearchLimiter;

    public function __construct(
        ManagerRegistry $doctrine,
        PaginatorInterface $paginator,
        BreadcrumbsMaker $breadcrumbsMaker,
        FinalCategoryFinder $finalCategoryFinder,
        SearchManager $searchManager,
        Searcher $searcher,
        Pdf $pdf,
        Breadcrumbs $breadcrumbs,
        CacheInterface $cache,
        RateLimiterFactory $livesearchLimiter
    ) {
        $this->doctrine = $doctrine;
        $this->paginator = $paginator;
        $this->breadcrumbsMaker = $breadcrumbsMaker;
        $this->finalCategoryFinder = $finalCategoryFinder;
        $this->searchManager = $searchManager;
        $this->searcher = $searcher;
        $this->pdf = $pdf;
        $this->breadcrumbs = $breadcrumbs;
        $this->cache = $cache;
        $this->livesearchLimiter = $livesearchLimiter;
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
        $categories = $this->cache->get('site_menu_categories', function (ItemInterface $item): array {
            $item->expiresAfter(3600);
            return $this->doctrine->getManager()
                ->getRepository(Category::class)
                ->childrenHierarchy();
        });

        return $this->render("includes/menu.html.twig", ['links' => $categories]);
    }

    #[Route('/show_category/{slug}', name: 'show_category', methods: ['GET'])]
    public function showCategoryAction(#[MapEntity(mapping: ['slug' => 'title'])] Category $category): Response
    {
        $this->breadcrumbsMaker->makeBreadcrumbs($category);
        return $this->render("site/index.html.twig", [
            'apiUrl' => '/api/public/estates?category=' . $category->getSlug(),
        ]);
    }

    #[Route('/show_estate/{slug}', name: 'show_estate', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function showEstateAction(Request $request, $slug): Response
    {
        $em = $this->doctrine->getManager();
        $estate = $em->getRepository(Estate::class)->getEstateWithDistrictComment($slug);
        $this->breadcrumbsMaker->makeBreadcrumbs($estate->getCategory(), $estate);

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
        $searchForm = $this->createForm(SearchType::class, null, [
            'action'             => $this->generateUrl('site_search_result'),
            'categories_choices' => $finalCategories,
        ]);

        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = $searchForm->getData();
            /** @var Category $category */
            $category = $data['category'];
            /** @var District|null $district */
            $district = $data['district'];

            return $this->render('site/search_result.html.twig', [
                'categorySlug' => (string) $category->getSlug(),
                'districtSlug' => $district ? (string) $district->getSlug() : '',
                'price'        => (string) ($data['price'] ?? ''),
                'exceptFloor'  => !empty($data['except_floor']) ? '1' : '0',
            ]);
        }

        return $this->redirectToRoute('homepage');
    }

    #[Route('/menu_item', name: 'show_menu_item', methods: ['GET'])]
    public function showMenuItemAction(Request $request): Response
    {
        $menuitems = $this->cache->get('site_menu_items', function (ItemInterface $item): array {
            $item->expiresAfter(3600);
            return $this->doctrine->getManager()
                ->getRepository(MenuItem::class)
                ->findAll();
        });
        return $this->render('includes/menu_items.html.twig', array('items' => $menuitems));
    }

    #[Route('/description_menu/{id}', name: 'show_description_menu_item', methods: ['GET'])]
    public function showDescriptionMenuItem(Request $request, MenuItem $menuItem): Response
    {
        return $this->render('site/show_description_menu_item.html.twig', array('item' => $menuItem));
    }

    #[Route('/add_favorites/{estate}/{user}', name: 'add_estate_to_favorites', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function addEstateToFavoritesAction(
        #[MapEntity(mapping: ['estate' => 'slug'])] Estate $estate,
        #[MapEntity(mapping: ['user' => 'id'])] User $user,
    ): RedirectResponse {
        if ($this->getUser()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->doctrine->getManager();
        if (!$user->hasEstate($estate)) {
            $user->addEstate($estate);
            $em->persist($user);
            $em->flush();
        }

        return $this->redirectToRoute('show_estate', array('slug' => $estate->getSlug()));
    }

    #[Route('/delete_favorites/{estate}/{user}', name: 'delete_estate_from_favorites', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function deleteEstateFromFavoritesAction(
        #[MapEntity(mapping: ['estate' => 'slug'])] Estate $estate,
        #[MapEntity(mapping: ['user' => 'id'])] User $user,
    ): RedirectResponse {
        if ($this->getUser()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }
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
        $limiter = $this->livesearchLimiter->create($request->getClientIp());
        if (!$limiter->consume()->isAccepted()) {
            return new Response('Too Many Requests', 429);
        }

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
