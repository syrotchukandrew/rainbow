# React Phase 3 — Full Public Site Sections Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace five Twig-rendered public-site sections (estate listing, search results, estate info panel, login, register, reset-password) with React islands that fetch data from a new unauthenticated JSON API.

**Architecture:** React islands mount into `<div id="react-*">` placeholders in lean Twig shells; each component self-fetches from `/api/public/*` endpoints. The Symfony controllers are updated to pass `data-*` attributes (API URLs, CSRF tokens, initial props) to the mount divs. Server-side form submission and CSRF protection are preserved for auth forms.

**Tech Stack:** Symfony 6.4, React 18, TypeScript 5, Webpack Encore, Doctrine ORM, LiipImagineBundle, Symfony Form + Security components.

---

## File Map

| Action | Path | Responsibility |
|--------|------|----------------|
| Create | `src/AppBundle/Controller/Api/PublicEstateApiController.php` | Three public JSON endpoints: listing, search, detail |
| Create | `src/AppBundle/Tests/Controller/Api/PublicEstateApiControllerTest.php` | Functional tests for all three endpoints |
| Create | `assets/site/components/EstateListing.tsx` | Paginated estate listing island |
| Create | `assets/site/components/SearchResult.tsx` | Search results island |
| Create | `assets/site/components/EstateInfoPanel.tsx` | Sidebar info panel on estate detail page |
| Create | `assets/site/components/LoginForm.tsx` | Login form island |
| Create | `assets/site/components/RegisterForm.tsx` | Register form island |
| Create | `assets/site/components/ResetRequestForm.tsx` | Password-reset request island |
| Create | `templates/site/search_result.html.twig` | Twig shell for search results page |
| Modify | `templates/site/index.html.twig` | Add React shell alongside Twig fallback |
| Modify | `templates/site/show_estate.html.twig` | Replace static sidebar with React mount div |
| Modify | `templates/security/login.html.twig` | Add React mount div + site bundle script |
| Modify | `templates/security/register.html.twig` | Add React mount div + site bundle script |
| Modify | `templates/security/reset_request.html.twig` | Add React mount div + site bundle script |
| Modify | `src/AppBundle/Controller/SiteController.php` | Update indexAction, showCategoryAction, searchResultAction |
| Modify | `assets/site/entry.tsx` | Mount new React islands |

---

## Task 1: PublicEstateApiController

**Files:**
- Create: `src/AppBundle/Controller/Api/PublicEstateApiController.php`
- Create: `src/AppBundle/Tests/Controller/Api/PublicEstateApiControllerTest.php`

### Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/public/estates` | Exclusive estates OR category-filtered estates; `?page=1&category=slug` |
| GET | `/api/public/search` | Search results; `?category=slug&district=slug&price=to_20000&except_floor=1&page=1` |
| GET | `/api/public/estates/{slug}` | Single estate with district, floor, images |

All endpoints are unauthenticated. Pagination shape: `{data, total, page, perPage}` with `perPage=5`.

---

- [ ] **Step 1: Write the failing tests**

Create `src/AppBundle/Tests/Controller/Api/PublicEstateApiControllerTest.php`:

```php
<?php

declare(strict_types=1);

namespace AppBundle\Tests\Controller\Api;

use AppBundle\Entity\Category;
use AppBundle\Entity\Estate;
use AppBundle\Tests\Controller\BaseTestController;

class PublicEstateApiControllerTest extends BaseTestController
{
    public function testEstateListingReturnsPagedResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/estates');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('perPage', $data);
        $this->assertIsArray($data['data']);
        $this->assertEquals(1, $data['page']);
        $this->assertEquals(5, $data['perPage']);
    }

    public function testEstateListingWithCategorySlug(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $category = $em->getRepository(Category::class)->findOneBy([]);
        $this->assertNotNull($category);

        $client->request('GET', '/api/public/estates?category=' . $category->getSlug());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('total', $data);
    }

    public function testEstateListingWithUnknownCategoryReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/estates?category=no-such-category-xyz');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testSearchRequiresCategoryParam(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/search');
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testSearchReturnsPagedResponse(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $category = $em->getRepository(Category::class)->findOneBy([]);
        $this->assertNotNull($category);

        $client->request('GET', '/api/public/search?category=' . $category->getSlug());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('perPage', $data);
        $this->assertIsArray($data['data']);
    }

    public function testSearchWithUnknownCategoryReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/search?category=no-such-category-xyz');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testEstateDetailReturnsEstate(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $estate = $em->getRepository(Estate::class)->findOneBy([]);
        $this->assertNotNull($estate);

        $client->request('GET', '/api/public/estates/' . $estate->getSlug());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('slug', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('floor', $data);
        $this->assertArrayHasKey('district', $data);
        $this->assertArrayHasKey('imageUrls', $data);
    }

    public function testEstateDetailWithUnknownSlugReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/estates/no-such-estate-xyz');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php bin/phpunit -c app/ src/AppBundle/Tests/Controller/Api/PublicEstateApiControllerTest.php
```

Expected: all tests fail with 404 (route not found).

- [ ] **Step 3: Implement the controller**

Create `src/AppBundle/Controller/Api/PublicEstateApiController.php`:

```php
<?php

declare(strict_types=1);

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Category;
use AppBundle\Entity\District;
use AppBundle\Entity\Estate;
use AppBundle\Utils\SearchManager;
use Doctrine\Persistence\ManagerRegistry;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/public')]
class PublicEstateApiController extends AbstractController
{
    private const PER_PAGE = 5;

    public function __construct(
        private ManagerRegistry $doctrine,
        private CacheManager $imagineCacheManager,
        private SearchManager $searchManager,
    ) {}

    #[Route('/estates', name: 'api_public_estates', methods: ['GET'])]
    public function estatesAction(Request $request): JsonResponse
    {
        $em = $this->doctrine->getManager();
        $categorySlug = $request->query->get('category');
        $page = max(1, $request->query->getInt('page', 1));

        if ($categorySlug !== null) {
            $category = $em->getRepository(Category::class)->findOneBy(['slug' => $categorySlug]);
            if ($category === null) {
                return $this->json(['error' => 'Category not found', 'code' => 404], 404);
            }
            $estates = $em->getRepository(Estate::class)->getEstateFromCategory($category->getTitle());
        } else {
            $estates = $em->getRepository(Estate::class)->getEstateExclusiveWithFiles();
        }

        $total = count($estates);
        $slice = array_slice($estates, ($page - 1) * self::PER_PAGE, self::PER_PAGE);

        return $this->json([
            'data'    => array_map(fn(Estate $e) => $this->serializeEstateSummary($e), $slice),
            'total'   => $total,
            'page'    => $page,
            'perPage' => self::PER_PAGE,
        ]);
    }

    #[Route('/search', name: 'api_public_search', methods: ['GET'])]
    public function searchAction(Request $request): JsonResponse
    {
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

        $estates = $this->searchManager->searchEstate([
            'category'     => $category,
            'district'     => $district,
            'price'        => $request->query->get('price', ''),
            'except_floor' => (bool) $request->query->getInt('except_floor', 0),
        ]);

        $total = count($estates);
        $slice = array_slice($estates, ($page - 1) * self::PER_PAGE, self::PER_PAGE);

        return $this->json([
            'data'    => array_map(fn(Estate $e) => $this->serializeEstateSummary($e), $slice),
            'total'   => $total,
            'page'    => $page,
            'perPage' => self::PER_PAGE,
        ]);
    }

    #[Route('/estates/{slug}', name: 'api_public_estate_detail', methods: ['GET'])]
    public function estateDetailAction(string $slug): JsonResponse
    {
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
            'firstLastFloor' => $estate->getFirstLastFloor(),
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
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
php bin/phpunit -c app/ src/AppBundle/Tests/Controller/Api/PublicEstateApiControllerTest.php
```

Expected: all 8 tests pass.

- [ ] **Step 5: Commit**

```bash
git add src/AppBundle/Controller/Api/PublicEstateApiController.php \
        src/AppBundle/Tests/Controller/Api/PublicEstateApiControllerTest.php
git commit -m "Added PublicEstateApiController with listing, search, and detail endpoints"
```

---

## Task 2: EstateListing Island

**Files:**
- Create: `assets/site/components/EstateListing.tsx`
- Modify: `templates/site/index.html.twig`
- Modify: `src/AppBundle/Controller/SiteController.php` (`indexAction`, `showCategoryAction`)
- Modify: `assets/site/entry.tsx`

- [ ] **Step 1: Create EstateListing.tsx**

Create `assets/site/components/EstateListing.tsx`:

```tsx
import React, { useEffect, useState } from 'react';

interface EstateSummary {
    id: number;
    slug: string;
    title: string;
    description: string;
    price: number | null;
    primaryImageUrl: string | null;
    secondaryImageUrls: string[];
}

interface PagedResponse {
    data: EstateSummary[];
    total: number;
    page: number;
    perPage: number;
}

interface Props {
    apiUrl: string;
}

export default function EstateListing({ apiUrl }: Props) {
    const [result, setResult] = useState<PagedResponse | null>(null);
    const [page, setPage] = useState(1);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        const sep = apiUrl.includes('?') ? '&' : '?';
        fetch(`${apiUrl}${sep}page=${page}`)
            .then(r => r.json())
            .then((data: PagedResponse) => {
                setResult(data);
                setLoading(false);
            });
    }, [apiUrl, page]);

    if (loading) return <p>Завантаження...</p>;
    if (!result || result.data.length === 0) return <p>Оголошення не знайдено.</p>;

    const totalPages = Math.ceil(result.total / result.perPage);

    return (
        <div className="panel">
            {result.data.map(estate => (
                <div key={estate.id}>
                    <h2>
                        <a className="grey" href={`/show_estate/${estate.slug}`}>{estate.title}</a>
                    </h2>
                    <div className="row">
                        <div className="col col-sm-8">
                            {estate.primaryImageUrl && (
                                <a href={`/show_estate/${estate.slug}`}>
                                    <img
                                        alt="фото нерухомості"
                                        src={estate.primaryImageUrl}
                                        className="img-responsive"
                                    />
                                </a>
                            )}
                        </div>
                        <div className="col col-sm-4">
                            {estate.secondaryImageUrls.map((url, i) => (
                                <React.Fragment key={i}>
                                    <a href={`/show_estate/${estate.slug}`}>
                                        <img alt="фото нерухомості" src={url} className="img-responsive" />
                                    </a>
                                    <hr />
                                </React.Fragment>
                            ))}
                        </div>
                    </div>
                    <h3>Опис:</h3>
                    <p>{estate.description}</p>
                    <a href={`/show_estate/${estate.slug}`} className="btn btn-default">
                        Детальніше
                    </a>
                    <hr />
                </div>
            ))}
            {totalPages > 1 && (
                <div className="navigation">
                    {Array.from({ length: totalPages }, (_, i) => i + 1).map(p => (
                        <button
                            key={p}
                            onClick={() => setPage(p)}
                            className={`btn btn-sm ${p === page ? 'btn-primary' : 'btn-default'}`}
                            style={{ margin: '0 2px' }}
                        >
                            {p}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
```

- [ ] **Step 2: Update index.html.twig**

Replace the full content of `templates/site/index.html.twig` with a version that renders the React shell when `apiUrl` is set, and falls back to the original Twig loop otherwise (preserving the livesearch POST path):

```twig
{% extends 'site/base_site.html.twig' %}
{% block panel %}
    {% if apiUrl is defined %}
        <div id="react-estate-listing" data-api-url="{{ apiUrl }}"></div>
    {% else %}
        <div class="panel">
            {% for estate in pagination %}
                <h2><a class="grey" href="{{ path('show_estate', { 'slug': estate.slug }) }}">{{ estate.title }}</a>
                </h2>

                <div class="row">
                    <div class="col col-sm-8">
                        {% if estate.mainFoto is not null %}
                            <a href="{{ path('show_estate', { 'slug': estate.slug }) }}">
                                <img alt="фото квартиры купить Черкассы" src="{{ asset(estate.mainFoto.path) |imagine_filter('large') }}"
                                     class="img-responsive">
                            </a>
                        {% else %}
                            <a href="{{ path('show_estate', { 'slug': estate.slug }) }}">
                                <img alt="фото квартиры купить Черкассы" src="{{ asset(estate.files[0].path) |imagine_filter('large') }}"
                                     class="img-responsive">
                            </a>
                        {% endif %}
                    </div>
                    <div class="col col-sm-4">
                        {% if estate.files[1] is defined %}
                            {% if estate.files[1] != estate.mainFoto %}
                                <a href="{{ path('show_estate', { 'slug': estate.slug }) }}">
                                    <img alt="фото квартиры купить Черкассы" src="{{ asset("#{estate.files[1].path}") |imagine_filter('medium') }}"
                                         class="img-responsive">
                                </a>
                            {% endif %}
                        {% endif %}
                        <hr>
                        {% if estate.files[2] is defined %}
                            <a href="{{ path('show_estate', { 'slug': estate.slug }) }}">
                                <img alt="фото квартиры купить Черкассы" src="{{ asset("#{estate.files[2].path}") |imagine_filter('medium') }}"
                                     class="img-responsive">
                            </a>
                        {% endif %}
                        <hr>
                    </div>
                </div>

                <h3>{{ 'site.summary'|trans }}:</h3>
                {{ estate.description }}
                <br><br>
                <a href="{{ path('show_estate', { 'slug': estate.slug }) }}"
                   class="btn btn-default">
                    {{ 'site.more'|trans }}
                </a>
                <hr>
            {% endfor %}
            <div class="navigation">
                {{ knp_pagination_render(pagination) }}
            </div>
        </div>
    {% endif %}
{% endblock panel %}
```

- [ ] **Step 3: Update SiteController — indexAction and showCategoryAction**

In `src/AppBundle/Controller/SiteController.php`, replace `indexAction`:

```php
#[Route('/', name: 'homepage', methods: ['GET'])]
public function indexAction(Request $request): Response
{
    $this->breadcrumbs->addItem("site.main");
    return $this->render("site/index.html.twig", ['apiUrl' => '/api/public/estates']);
}
```

Replace `showCategoryAction`:

```php
#[Route('/show_category/{slug}', name: 'show_category', methods: ['GET'])]
public function showCategoryAction(Request $request, #[MapEntity(mapping: ['slug' => 'title'])] Category $category): Response
{
    $this->breadcrumpsMaker->makeBreadcrumps($category);
    return $this->render("site/index.html.twig", [
        'apiUrl' => '/api/public/estates?category=' . $category->getSlug(),
    ]);
}
```

- [ ] **Step 4: Mount EstateListing in entry.tsx**

In `assets/site/entry.tsx`, add after the existing imports:

```tsx
import EstateListing from './components/EstateListing';
```

And add the mount block after the existing island mounts:

```tsx
// EstateListing
const estateListingEl = document.getElementById('react-estate-listing');
if (estateListingEl) {
    const apiUrl = estateListingEl.dataset.apiUrl ?? '/api/public/estates';
    mountIsland('react-estate-listing', EstateListing, { apiUrl });
}
```

- [ ] **Step 5: Clear cache and verify homepage loads**

```bash
php bin/console cache:clear
```

Open `http://localhost/` in a browser. The page should load with the React estate listing component (or "Завантаження..." then estate cards). No PHP errors in logs.

- [ ] **Step 6: Run full test suite**

```bash
php bin/phpunit -c app/
```

Expected: all tests pass.

- [ ] **Step 7: Commit**

```bash
git add assets/site/components/EstateListing.tsx \
        templates/site/index.html.twig \
        src/AppBundle/Controller/SiteController.php \
        assets/site/entry.tsx
git commit -m "Added EstateListing React island; updated index.html.twig and SiteController"
```

---

## Task 3: SearchResult Island

**Files:**
- Create: `assets/site/components/SearchResult.tsx`
- Create: `templates/site/search_result.html.twig`
- Modify: `src/AppBundle/Controller/SiteController.php` (`searchResultAction`)
- Modify: `assets/site/entry.tsx`

- [ ] **Step 1: Create SearchResult.tsx**

Create `assets/site/components/SearchResult.tsx`:

```tsx
import React, { useEffect, useState } from 'react';

interface EstateSummary {
    id: number;
    slug: string;
    title: string;
    description: string;
    price: number | null;
    primaryImageUrl: string | null;
    secondaryImageUrls: string[];
}

interface PagedResponse {
    data: EstateSummary[];
    total: number;
    page: number;
    perPage: number;
}

interface Props {
    categorySlug: string;
    districtSlug: string;
    price: string;
    exceptFloor: string;
}

export default function SearchResult({ categorySlug, districtSlug, price, exceptFloor }: Props) {
    const [result, setResult] = useState<PagedResponse | null>(null);
    const [page, setPage] = useState(1);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        const params = new URLSearchParams({ category: categorySlug, page: String(page) });
        if (districtSlug) params.set('district', districtSlug);
        if (price) params.set('price', price);
        if (exceptFloor === '1') params.set('except_floor', '1');

        fetch(`/api/public/search?${params.toString()}`)
            .then(r => r.json())
            .then((data: PagedResponse) => {
                setResult(data);
                setLoading(false);
            });
    }, [categorySlug, districtSlug, price, exceptFloor, page]);

    if (loading) return <p>Завантаження...</p>;
    if (!result || result.data.length === 0) return <p>За вашим запитом нічого не знайдено.</p>;

    const totalPages = Math.ceil(result.total / result.perPage);

    return (
        <div className="panel">
            {result.data.map(estate => (
                <div key={estate.id}>
                    <h2>
                        <a className="grey" href={`/show_estate/${estate.slug}`}>{estate.title}</a>
                    </h2>
                    <div className="row">
                        <div className="col col-sm-8">
                            {estate.primaryImageUrl && (
                                <a href={`/show_estate/${estate.slug}`}>
                                    <img
                                        alt="фото нерухомості"
                                        src={estate.primaryImageUrl}
                                        className="img-responsive"
                                    />
                                </a>
                            )}
                        </div>
                        <div className="col col-sm-4">
                            {estate.secondaryImageUrls.map((url, i) => (
                                <React.Fragment key={i}>
                                    <a href={`/show_estate/${estate.slug}`}>
                                        <img alt="фото нерухомості" src={url} className="img-responsive" />
                                    </a>
                                    <hr />
                                </React.Fragment>
                            ))}
                        </div>
                    </div>
                    <h3>Опис:</h3>
                    <p>{estate.description}</p>
                    <a href={`/show_estate/${estate.slug}`} className="btn btn-default">
                        Детальніше
                    </a>
                    <hr />
                </div>
            ))}
            {totalPages > 1 && (
                <div className="navigation">
                    {Array.from({ length: totalPages }, (_, i) => i + 1).map(p => (
                        <button
                            key={p}
                            onClick={() => setPage(p)}
                            className={`btn btn-sm ${p === page ? 'btn-primary' : 'btn-default'}`}
                            style={{ margin: '0 2px' }}
                        >
                            {p}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
```

- [ ] **Step 2: Create search_result.html.twig**

Create `templates/site/search_result.html.twig`:

```twig
{% extends 'site/base_site.html.twig' %}
{% block panel %}
    <div id="react-search-results"
         data-category="{{ categorySlug }}"
         data-district="{{ districtSlug }}"
         data-price="{{ price }}"
         data-except-floor="{{ exceptFloor }}">
    </div>
{% endblock panel %}
```

- [ ] **Step 3: Update SiteController — searchResultAction**

Replace `searchResultAction` in `src/AppBundle/Controller/SiteController.php`:

```php
#[Route('/search/result', name: 'site_search_result', methods: ['GET', 'POST'])]
public function searchResultAction(Request $request): Response
{
    $finalCategories = $this->finalCategoryFinder->findFinalCategories();
    $searchForm = $this->createForm(SearchType::class, null, [
        'action'             => $this->generateUrl('site_search_result'),
        'categories_choices' => $finalCategories,
    ]);

    $searchForm->handleRequest($request);
    if ($searchForm->isValid() && $searchForm->isSubmitted()) {
        $data = $searchForm->getData();
        /** @var \AppBundle\Entity\Category $category */
        $category = $data['category'];
        /** @var \AppBundle\Entity\District|null $district */
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
```

- [ ] **Step 4: Mount SearchResult in entry.tsx**

Add import:

```tsx
import SearchResult from './components/SearchResult';
```

Add mount block:

```tsx
// SearchResult
const searchResultsEl = document.getElementById('react-search-results');
if (searchResultsEl) {
    const categorySlug = searchResultsEl.dataset.category ?? '';
    const districtSlug = searchResultsEl.dataset.district ?? '';
    const price        = searchResultsEl.dataset.price ?? '';
    const exceptFloor  = searchResultsEl.dataset.exceptFloor ?? '0';
    mountIsland('react-search-results', SearchResult, { categorySlug, districtSlug, price, exceptFloor });
}
```

- [ ] **Step 5: Clear cache and manually test search**

```bash
php bin/console cache:clear
```

Submit the search form from the site header. Expect to land on a page showing the React search results component.

- [ ] **Step 6: Run full test suite**

```bash
php bin/phpunit -c app/
```

Expected: all tests pass.

- [ ] **Step 7: Commit**

```bash
git add assets/site/components/SearchResult.tsx \
        templates/site/search_result.html.twig \
        src/AppBundle/Controller/SiteController.php \
        assets/site/entry.tsx
git commit -m "Added SearchResult React island and search_result.html.twig shell"
```

---

## Task 4: EstateInfoPanel Island

**Files:**
- Create: `assets/site/components/EstateInfoPanel.tsx`
- Modify: `templates/site/show_estate.html.twig`
- Modify: `assets/site/entry.tsx`

- [ ] **Step 1: Create EstateInfoPanel.tsx**

Create `assets/site/components/EstateInfoPanel.tsx`:

```tsx
import React, { useEffect, useState } from 'react';

interface District {
    id: number;
    title: string;
    slug: string;
}

interface EstateDetail {
    id: number;
    slug: string;
    title: string;
    price: number | null;
    floor: { floor: number; count_floor: number } | null;
    firstLastFloor: boolean | null;
    district: District | null;
}

interface Props {
    slug: string;
}

export default function EstateInfoPanel({ slug }: Props) {
    const [estate, setEstate] = useState<EstateDetail | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetch(`/api/public/estates/${slug}`)
            .then(r => r.json())
            .then((data: EstateDetail) => {
                setEstate(data);
                setLoading(false);
            });
    }, [slug]);

    if (loading) return <p>Завантаження...</p>;
    if (!estate) return null;

    return (
        <>
            <div className="panel panel-default">
                <div className="panel-heading">
                    <i className="glyphicon glyphicon-stats" /> Ціна
                </div>
                <div className="panel-body">
                    {estate.price !== null ? `${estate.price}\u00a0дол.` : '—'}
                </div>
            </div>
            {estate.district && (
                <div className="panel panel-default">
                    <div className="panel-heading">
                        <i className="glyphicon glyphicon-stats" /> Район
                    </div>
                    <div className="panel-body">
                        {estate.district.title}
                    </div>
                </div>
            )}
            {estate.floor && (
                <div className="panel panel-default">
                    <div className="panel-heading">
                        <i className="glyphicon glyphicon-stats" /> Поверх / Поверховість
                    </div>
                    <div className="panel-body">
                        {estate.floor.floor} / {estate.floor.count_floor}
                    </div>
                </div>
            )}
        </>
    );
}
```

- [ ] **Step 2: Update show_estate.html.twig sidebar**

In `templates/site/show_estate.html.twig`, replace the entire `<div class="col-lg-3">` block (lines 67–99) with:

```twig
    <div class="col-lg-3">
        <div id="react-estate-info-panel" data-slug="{{ estate.slug }}"></div>
        <ul>
            <li>{{ googlePlusButton() }}</li>
            <li>{{ twitterButton() }}</li>
            <li>{{ facebookButton() }}</li>
        </ul>
    </div>
```

- [ ] **Step 3: Mount EstateInfoPanel in entry.tsx**

Add import:

```tsx
import EstateInfoPanel from './components/EstateInfoPanel';
```

Add mount block:

```tsx
// EstateInfoPanel
const estateInfoPanelEl = document.getElementById('react-estate-info-panel');
if (estateInfoPanelEl) {
    const slug = estateInfoPanelEl.dataset.slug ?? '';
    mountIsland('react-estate-info-panel', EstateInfoPanel, { slug });
}
```

- [ ] **Step 4: Clear cache and verify estate detail page**

```bash
php bin/console cache:clear
```

Navigate to any estate detail page (e.g., `/show_estate/{some-slug}`). The right sidebar should show the React price/district/floor panels. No PHP errors.

- [ ] **Step 5: Run full test suite**

```bash
php bin/phpunit -c app/
```

Expected: all tests pass.

- [ ] **Step 6: Commit**

```bash
git add assets/site/components/EstateInfoPanel.tsx \
        templates/site/show_estate.html.twig \
        assets/site/entry.tsx
git commit -m "Added EstateInfoPanel React island; replaced static sidebar in show_estate.html.twig"
```

---

## Task 5: Auth Page React Islands

**Files:**
- Create: `assets/site/components/LoginForm.tsx`
- Create: `assets/site/components/RegisterForm.tsx`
- Create: `assets/site/components/ResetRequestForm.tsx`
- Modify: `templates/security/login.html.twig`
- Modify: `templates/security/register.html.twig`
- Modify: `templates/security/reset_request.html.twig`
- Modify: `assets/site/entry.tsx`

**CSRF note:**
- Login form: uses Symfony security's `csrf_token('authenticate')`. Hidden field name: `_csrf_token`.
- Register form: uses Symfony FormType CSRF. Controller still creates the form; template reads `form.vars.token.vars.value`. Hidden field name: `app_bundle_user_type[_token]`.
- Reset request form: same pattern as register. Hidden field name: `password_reset_request_type[_token]`.

Auth templates extend `base.html.twig` (not `site/base_site.html.twig`), so the site bundle must be added explicitly via `{% block javascripts %}`.

- [ ] **Step 1: Create LoginForm.tsx**

Create `assets/site/components/LoginForm.tsx`:

```tsx
import React, { useState } from 'react';

interface Props {
    action: string;
    csrf: string;
    error: string;
    lastUsername: string;
}

export default function LoginForm({ action, csrf, error, lastUsername }: Props) {
    const [username, setUsername] = useState(lastUsername);
    const [password, setPassword] = useState('');

    return (
        <div className="row">
            <div className="col-sm-5">
                <div className="well">
                    {error && (
                        <div className="alert alert-danger">{error}</div>
                    )}
                    <form method="POST" action={action}>
                        <div className="form-group">
                            <label htmlFor="login_username">Логін</label>
                            <input
                                id="login_username"
                                type="text"
                                name="_username"
                                value={username}
                                onChange={e => setUsername(e.target.value)}
                                className="form-control"
                                autoFocus
                            />
                        </div>
                        <div className="form-group">
                            <label htmlFor="login_password">Пароль</label>
                            <input
                                id="login_password"
                                type="password"
                                name="_password"
                                value={password}
                                onChange={e => setPassword(e.target.value)}
                                className="form-control"
                            />
                        </div>
                        <input type="hidden" name="_csrf_token" value={csrf} />
                        <button type="submit" className="btn btn-primary">Увійти</button>
                    </form>
                </div>
            </div>
        </div>
    );
}
```

- [ ] **Step 2: Create RegisterForm.tsx**

Create `assets/site/components/RegisterForm.tsx`:

```tsx
import React, { useState } from 'react';

interface Props {
    action: string;
    csrf: string;
    errors: string[];
}

export default function RegisterForm({ action, csrf, errors }: Props) {
    const [username, setUsername]         = useState('');
    const [email, setEmail]               = useState('');
    const [password, setPassword]         = useState('');
    const [passwordRepeat, setRepeat]     = useState('');

    return (
        <div className="row">
            <div className="col-sm-5">
                <div className="well">
                    {errors.length > 0 && (
                        <div className="alert alert-danger">
                            <ul className="mb-0">
                                {errors.map((e, i) => <li key={i}>{e}</li>)}
                            </ul>
                        </div>
                    )}
                    <form method="POST" action={action}>
                        <div className="form-group">
                            <label htmlFor="reg_username">Логін</label>
                            <input
                                id="reg_username"
                                type="text"
                                name="app_bundle_user_type[username]"
                                value={username}
                                onChange={e => setUsername(e.target.value)}
                                className="form-control"
                            />
                        </div>
                        <div className="form-group">
                            <label htmlFor="reg_email">Email</label>
                            <input
                                id="reg_email"
                                type="email"
                                name="app_bundle_user_type[email]"
                                value={email}
                                onChange={e => setEmail(e.target.value)}
                                className="form-control"
                            />
                        </div>
                        <div className="form-group">
                            <label htmlFor="reg_password">Пароль</label>
                            <input
                                id="reg_password"
                                type="password"
                                name="app_bundle_user_type[plainPassword][first]"
                                value={password}
                                onChange={e => setPassword(e.target.value)}
                                className="form-control"
                            />
                        </div>
                        <div className="form-group">
                            <label htmlFor="reg_password_repeat">Повторіть пароль</label>
                            <input
                                id="reg_password_repeat"
                                type="password"
                                name="app_bundle_user_type[plainPassword][second]"
                                value={passwordRepeat}
                                onChange={e => setRepeat(e.target.value)}
                                className="form-control"
                            />
                        </div>
                        <input type="hidden" name="app_bundle_user_type[_token]" value={csrf} />
                        <button type="submit" className="btn btn-primary">Зареєструватись!</button>
                    </form>
                </div>
            </div>
        </div>
    );
}
```

- [ ] **Step 3: Create ResetRequestForm.tsx**

Create `assets/site/components/ResetRequestForm.tsx`:

```tsx
import React, { useState } from 'react';

interface Props {
    action: string;
    csrf: string;
    loginUrl: string;
}

export default function ResetRequestForm({ action, csrf, loginUrl }: Props) {
    const [email, setEmail] = useState('');

    return (
        <div className="row">
            <div className="col-sm-5">
                <div className="well">
                    <h2>Відновлення паролю</h2>
                    <form method="POST" action={action}>
                        <div className="form-group">
                            <label htmlFor="reset_email">Email</label>
                            <input
                                id="reset_email"
                                type="email"
                                name="password_reset_request_type[email]"
                                value={email}
                                onChange={e => setEmail(e.target.value)}
                                className="form-control"
                            />
                        </div>
                        <input type="hidden" name="password_reset_request_type[_token]" value={csrf} />
                        <button type="submit" className="btn btn-primary">Надіслати</button>
                    </form>
                    <a href={loginUrl}>Увійти</a>
                </div>
            </div>
        </div>
    );
}
```

- [ ] **Step 4: Update login.html.twig**

Replace the full content of `templates/security/login.html.twig`:

```twig
{% extends 'base.html.twig' %}

{% block javascripts %}
    {{ parent() }}
    {{ encore_entry_script_tags('site') }}
{% endblock %}

{% block body %}
    <div id="react-login-form"
         data-action="{{ path('security_login_check') }}"
         data-csrf="{{ csrf_token('authenticate') }}"
         data-error="{{ error ? error.messageKey|trans(error.messageData, 'security') : '' }}"
         data-last-username="{{ last_username }}">
    </div>
{% endblock %}

{% block sidebar %}{% endblock %}
```

- [ ] **Step 5: Update register.html.twig**

Replace the full content of `templates/security/register.html.twig`:

```twig
{% extends 'base.html.twig' %}

{% block javascripts %}
    {{ parent() }}
    {{ encore_entry_script_tags('site') }}
{% endblock %}

{% block body %}
    {% set formErrors = [] %}
    {% for error in form.vars.errors %}
        {% set formErrors = formErrors|merge([error.message]) %}
    {% endfor %}
    {% for field in form.children %}
        {% for error in field.vars.errors %}
            {% set formErrors = formErrors|merge([error.message]) %}
        {% endfor %}
    {% endfor %}
    <div id="react-register-form"
         data-action="{{ path('user_registration') }}"
         data-csrf="{{ form.vars.token.vars.value }}"
         data-errors="{{ formErrors|json_encode }}">
    </div>
{% endblock %}

{% block sidebar %}{% endblock %}
```

- [ ] **Step 6: Update reset_request.html.twig**

Replace the full content of `templates/security/reset_request.html.twig`:

```twig
{% extends 'base.html.twig' %}

{% block javascripts %}
    {{ parent() }}
    {{ encore_entry_script_tags('site') }}
{% endblock %}

{% block body %}
    <div id="react-reset-request-form"
         data-action="{{ path('security_reset_request') }}"
         data-csrf="{{ form.vars.token.vars.value }}"
         data-login-url="{{ path('security_login_form') }}">
    </div>
{% endblock %}
```

- [ ] **Step 7: Mount auth islands in entry.tsx**

Add imports:

```tsx
import LoginForm from './components/LoginForm';
import RegisterForm from './components/RegisterForm';
import ResetRequestForm from './components/ResetRequestForm';
```

Add mount blocks:

```tsx
// LoginForm
const loginFormEl = document.getElementById('react-login-form');
if (loginFormEl) {
    const action       = loginFormEl.dataset.action ?? '';
    const csrf         = loginFormEl.dataset.csrf ?? '';
    const error        = loginFormEl.dataset.error ?? '';
    const lastUsername = loginFormEl.dataset.lastUsername ?? '';
    mountIsland('react-login-form', LoginForm, { action, csrf, error, lastUsername });
}

// RegisterForm
const registerFormEl = document.getElementById('react-register-form');
if (registerFormEl) {
    const action = registerFormEl.dataset.action ?? '';
    const csrf   = registerFormEl.dataset.csrf ?? '';
    const errors: string[] = JSON.parse(registerFormEl.dataset.errors ?? '[]');
    mountIsland('react-register-form', RegisterForm, { action, csrf, errors });
}

// ResetRequestForm
const resetFormEl = document.getElementById('react-reset-request-form');
if (resetFormEl) {
    const action   = resetFormEl.dataset.action ?? '';
    const csrf     = resetFormEl.dataset.csrf ?? '';
    const loginUrl = resetFormEl.dataset.loginUrl ?? '';
    mountIsland('react-reset-request-form', ResetRequestForm, { action, csrf, loginUrl });
}
```

- [ ] **Step 8: Clear cache and verify auth pages**

```bash
php bin/console cache:clear
```

Verify each page loads without error:
- `/login` — React form renders, login works
- `/register` — React form renders, register works, validation errors display on failed submit
- `/reset-password` — React form renders, submission works

- [ ] **Step 9: Run full test suite**

```bash
php bin/phpunit -c app/
```

Expected: all tests pass.

- [ ] **Step 10: Commit**

```bash
git add assets/site/components/LoginForm.tsx \
        assets/site/components/RegisterForm.tsx \
        assets/site/components/ResetRequestForm.tsx \
        templates/security/login.html.twig \
        templates/security/register.html.twig \
        templates/security/reset_request.html.twig \
        assets/site/entry.tsx
git commit -m "Added auth React islands for login, register, and reset-request pages"
```

---

## Self-Review

**Spec coverage check:**
- ✅ Auth pages (login, register, reset password) — Task 5
- ✅ Search results (filters + paginated list) — Tasks 1 + 3
- ✅ Estate listing (homepage + category pages) — Tasks 1 + 2
- ✅ Estate detail (info panel) — Tasks 1 + 4
- ✅ API endpoints for all data — Task 1
- ✅ TDD for API layer — Task 1

**Placeholder scan:** No TBD, TODO, or "similar to" shortcuts. Every step has complete code.

**Type consistency:**
- `EstateSummary` interface used identically in `EstateListing.tsx` and `SearchResult.tsx`
- `serializeEstateSummary()` in controller returns `primaryImageUrl` + `secondaryImageUrls`, matching both components
- Auth field names (`app_bundle_user_type[*]`) match Symfony's `UserType::getBlockPrefix()`
- `password_reset_request_type[*]` matches `PasswordResetRequestType` default prefix
