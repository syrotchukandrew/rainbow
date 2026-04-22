<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Category;
use App\Entity\District;
use App\Entity\Estate;
use App\Repository\EstateRepository;
use App\Utils\SearchManager;
use Doctrine\Persistence\ManagerRegistry;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/public')]
class PublicEstateApiController extends AbstractController
{
    private const int PER_PAGE = 5;

    public function __construct(
        private ManagerRegistry $doctrine,
        private CacheManager $imagineCacheManager,
        private SearchManager $searchManager,
        private RateLimiterFactory $apiPublicLimiter,
    ) {}

    private function checkRateLimit(Request $request): ?JsonResponse
    {
        $limiter = $this->apiPublicLimiter->create($request->getClientIp());
        if (!$limiter->consume()->isAccepted()) {
            return $this->json(['error' => 'Too Many Requests', 'code' => 429], 429);
        }
        return null;
    }

    #[Route('/estates', name: 'api_public_estates', methods: ['GET'])]
    public function estatesAction(Request $request): JsonResponse
    {
        if ($rateLimitResponse = $this->checkRateLimit($request)) {
            return $rateLimitResponse;
        }
        $em = $this->doctrine->getManager();
        $categorySlug = $request->query->get('category');
        $page = max(1, $request->query->getInt('page', 1));

        /** @var EstateRepository $estateRepo */
        $estateRepo = $em->getRepository(Estate::class);

        if ($categorySlug !== null) {
            $category = $em->getRepository(Category::class)->findOneBy(['slug' => $categorySlug]);
            if ($category === null) {
                return $this->json(['error' => 'Category not found', 'code' => 404], 404);
            }
            $estates = $estateRepo->findByCategoryTitlePaginated($category->getTitle(), $page, self::PER_PAGE);
            $total = $estateRepo->countByCategoryTitle($category->getTitle());
        } else {
            $estates = $estateRepo->findPublishedPaginated($page, self::PER_PAGE);
            $total = $estateRepo->countPublished();
        }

        return $this->json([
            'data'    => array_map(fn(Estate $e) => $this->serializeEstateSummary($e), $estates),
            'total'   => $total,
            'page'    => $page,
            'perPage' => self::PER_PAGE,
        ]);
    }

    #[Route('/search', name: 'api_public_search', methods: ['GET'])]
    public function searchAction(Request $request): JsonResponse
    {
        if ($rateLimitResponse = $this->checkRateLimit($request)) {
            return $rateLimitResponse;
        }
        $em = $this->doctrine->getManager();
        $categorySlug = $request->query->get('category');
        $page = max(1, $request->query->getInt('page', 1));

        if ($categorySlug === null) {
            return $this->json(['error' => 'category parameter is required', 'code' => 400], 400);
        }

        $category = $em->getRepository(Category::class)->findOneBy(['slug' => $categorySlug]);
        if ($category === null) {
            return $this->json(['error' => 'Category not found', 'code' => 404], 404);
        }

        $district = null;
        $districtSlug = $request->query->get('district');
        if ($districtSlug !== null && $districtSlug !== '') {
            $district = $em->getRepository(District::class)->findOneBy(['slug' => $districtSlug]);
        }

        $searchData = [
            'category'     => $category,
            'district'     => $district,
            'price'        => $request->query->get('price', ''),
            'except_floor' => (bool) $request->query->getInt('except_floor', 0),
        ];

        $total = $this->searchManager->countEstate($searchData);
        $estates = $this->searchManager->searchEstatePaginated($searchData, $page, self::PER_PAGE);

        return $this->json([
            'data'    => array_map(fn(Estate $e) => $this->serializeEstateSummary($e), $estates),
            'total'   => $total,
            'page'    => $page,
            'perPage' => self::PER_PAGE,
        ]);
    }

    #[Route('/estates/{slug}', name: 'api_public_estate_detail', methods: ['GET'])]
    public function estateDetailAction(string $slug, Request $request): JsonResponse
    {
        if ($rateLimitResponse = $this->checkRateLimit($request)) {
            return $rateLimitResponse;
        }
        $em = $this->doctrine->getManager();
        $estate = $em->getRepository(Estate::class)->getEstateWithDistrictComment($slug);

        if ($estate === null) {
            return $this->json(['error' => 'Estate not found', 'code' => 404], 404);
        }

        $district = $estate->getDistrict();

        return $this->json([
            'id'             => $estate->getId(),
            'slug'           => $estate->getSlug(),
            'title'          => $estate->getTitle(),
            'price'          => $estate->getPrice(),
            'description'    => $estate->getDescription(),
            'floor'          => $estate->getFloor(),
            'firstLastFloor' => $estate->isFirstLastFloor(),
            'district'       => $district ? [
                'id'    => $district->getId(),
                'title' => $district->getTitle(),
                'slug'  => $district->getSlug(),
            ] : null,
            'imageUrls'      => $estate->getFiles()->map(
                fn($f) => $this->imagineCacheManager->getBrowserPath($f->getPath(), 'large')
            )->toArray(),
            'category'       => $estate->getCategory() ? [
                'id'    => $estate->getCategory()->getId(),
                'title' => $estate->getCategory()->getTitle(),
                'slug'  => $estate->getCategory()->getSlug(),
            ] : null,
        ]);
    }

    private function serializeEstateSummary(Estate $estate): array
    {
        $files = $estate->getFiles();
        $mainFoto = $estate->getMainFoto();

        $primaryPath = $mainFoto
            ? $mainFoto->getPath()
            : ($files->isEmpty() ? null : $files->first()->getPath());
        $primaryImageUrl = $primaryPath
            ? $this->imagineCacheManager->getBrowserPath($primaryPath, 'large')
            : null;

        $secondaryUrls = [];
        foreach ($files as $file) {
            if ($mainFoto !== null && $file->getId() === $mainFoto->getId()) {
                continue;
            }
            $secondaryUrls[] = $this->imagineCacheManager->getBrowserPath($file->getPath(), 'medium');
            if (count($secondaryUrls) >= 2) {
                break;
            }
        }

        return [
            'id'                 => $estate->getId(),
            'slug'               => $estate->getSlug(),
            'title'              => $estate->getTitle(),
            'description'        => $estate->getDescription(),
            'price'              => $estate->getPrice(),
            'primaryImageUrl'    => $primaryImageUrl,
            'secondaryImageUrls' => $secondaryUrls,
        ];
    }
}
