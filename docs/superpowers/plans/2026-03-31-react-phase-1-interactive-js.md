# React Phase 1 — Interactive JS → React Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace all legacy jQuery/vanilla-JS interactive widgets with React components mounted as islands, connecting them to new JSON API endpoints where needed.

**Architecture:** React islands mount into `<div id="react-*">` nodes inside existing Twig page shells. The public site gets four components (LiveSearch, EstateSlideshow, FavoriteButton, CommentForm); the admin panel gets three (AdminSidebar, CommentBadge, DataTable). Two new PHP API controllers provide the JSON endpoints that FavoriteButton, CommentForm, and CommentBadge require.

**Tech Stack:** React 19, TypeScript, Tailwind CSS, shadcn/ui, Symfony Webpack Encore, PHPUnit 10

---

## File Structure

### Created
- `src/AppBundle/Controller/Api/EstateApiController.php` — POST/DELETE `/api/estate/{slug}/favorite`, POST `/api/estate/{slug}/comment`
- `src/AppBundle/Controller/Api/AdminApiController.php` — GET `/api/admin/comments/pending-count`
- `tests/AppBundle/Controller/Api/EstateApiControllerTest.php`
- `tests/AppBundle/Controller/Api/AdminApiControllerTest.php`
- `assets/site/components/LiveSearch.tsx`
- `assets/site/components/EstateSlideshow.tsx`
- `assets/site/components/FavoriteButton.tsx`
- `assets/site/components/CommentForm.tsx`
- `assets/admin/components/AdminSidebar.tsx`
- `assets/admin/components/CommentBadge.tsx`
- `assets/admin/components/DataTable.tsx`

### Modified
- `assets/site/entry.tsx` — replace HelloIsland with real islands (Tasks 3–6)
- `assets/admin/entry.tsx` — add admin islands (Tasks 7–9)
- `templates/site/base_site.html.twig` — replace livesearch form with mount div (Task 3)
- `templates/site/show_estate.html.twig` — replace slideshow, favorite links, comment sub-render (Tasks 4–6)
- `templates/admin/base_admin.html.twig` — replace sidebar HTML and comment badge sub-render (Tasks 7–8)
- `templates/admin/estate/estates.html.twig` — add JSON data block, replace table with mount div (Task 9)

### Deleted
- `assets/site/components/HelloIsland.tsx` — smoke-test component removed in Task 3

---

## Task 1: Backend — Favorites & Comment API Controller

**Files:**
- Create: `src/AppBundle/Controller/Api/EstateApiController.php`
- Test: `tests/AppBundle/Controller/Api/EstateApiControllerTest.php`

- [ ] **Step 1: Write the failing tests**

Create `tests/AppBundle/Controller/Api/EstateApiControllerTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\AppBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EstateApiControllerTest extends WebTestCase
{
    public function testAddFavoriteRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/estate/test-estate/favorite', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => 'invalid',
        ]);
        $this->assertResponseRedirects();
    }

    public function testAddFavoriteReturnsFavoritedTrue(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getTestUser($client));

        $csrfToken = $this->getCsrfToken($client, 'api_estate_favorite');

        $client->request('POST', '/api/estate/test-estate/favorite', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => $csrfToken,
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['favorited']);
    }

    public function testRemoveFavoriteReturnsFavoritedFalse(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getTestUser($client));

        $csrfToken = $this->getCsrfToken($client, 'api_estate_favorite');

        // First add it
        $client->request('POST', '/api/estate/test-estate/favorite', [], [], [
            'HTTP_X-CSRF-Token' => $csrfToken,
        ]);

        // Then remove it
        $client->request('DELETE', '/api/estate/test-estate/favorite', [], [], [
            'HTTP_X-CSRF-Token' => $csrfToken,
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($data['favorited']);
    }

    public function testAddFavoriteRejectsInvalidCsrf(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getTestUser($client));

        $client->request('POST', '/api/estate/test-estate/favorite', [], [], [
            'HTTP_X-CSRF-Token' => 'bad-token',
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testPostCommentRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/estate/test-estate/comment', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['content' => 'Hello']));
        $this->assertResponseRedirects();
    }

    public function testPostCommentReturnsSuccess(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getTestUser($client));

        $csrfToken = $this->getCsrfToken($client, 'api_estate_comment');

        $client->request('POST', '/api/estate/test-estate/comment', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => $csrfToken,
        ], json_encode(['content' => 'This is a test comment from API']));

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
    }

    public function testPostCommentValidatesBlankContent(): void
    {
        $client = static::createClient();
        $client->loginUser($this->getTestUser($client));

        $csrfToken = $this->getCsrfToken($client, 'api_estate_comment');

        $client->request('POST', '/api/estate/test-estate/comment', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-CSRF-Token' => $csrfToken,
        ], json_encode(['content' => '']));

        $this->assertResponseStatusCodeSame(422);
    }

    private function getTestUser($client): \AppBundle\Entity\User
    {
        $em = $client->getContainer()->get('doctrine')->getManager();
        return $em->getRepository(\AppBundle\Entity\User::class)->findOneBy(['username' => 'user']);
    }

    private function getCsrfToken($client, string $tokenId): string
    {
        $csrfManager = $client->getContainer()->get('security.csrf.token_manager');
        return $csrfManager->getToken($tokenId)->getValue();
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
docker compose exec -T web php bin/phpunit -c app/ tests/AppBundle/Controller/Api/EstateApiControllerTest.php
```

Expected: errors (class not found / 404 responses).

- [ ] **Step 3: Create the controller**

Create `src/AppBundle/Controller/Api/EstateApiController.php`:

```php
<?php

declare(strict_types=1);

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Estate;
use AppBundle\Entity\Comment;
use AppBundle\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/api/estate')]
class EstateApiController extends AbstractController
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('/{slug}/favorite', name: 'api_estate_add_favorite', methods: ['POST'])]
    public function addFavorite(
        #[MapEntity(mapping: ['slug' => 'slug'])] Estate $estate,
        Request $request,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('api_estate_favorite', $request->headers->get('X-CSRF-Token', ''))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], Response::HTTP_FORBIDDEN);
        }

        /** @var User $user */
        $user = $this->getUser();
        if (!$user->hasEstate($estate)) {
            $user->addEstate($estate);
            $this->doctrine->getManager()->flush();
        }

        return new JsonResponse(['favorited' => true]);
    }

    #[Route('/{slug}/favorite', name: 'api_estate_remove_favorite', methods: ['DELETE'])]
    public function removeFavorite(
        #[MapEntity(mapping: ['slug' => 'slug'])] Estate $estate,
        Request $request,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('api_estate_favorite', $request->headers->get('X-CSRF-Token', ''))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], Response::HTTP_FORBIDDEN);
        }

        /** @var User $user */
        $user = $this->getUser();
        if ($user->hasEstate($estate)) {
            $user->removeEstate($estate);
            $this->doctrine->getManager()->flush();
        }

        return new JsonResponse(['favorited' => false]);
    }

    #[Route('/{slug}/comment', name: 'api_estate_post_comment', methods: ['POST'])]
    public function postComment(
        #[MapEntity(mapping: ['slug' => 'slug'])] Estate $estate,
        Request $request,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('api_estate_comment', $request->headers->get('X-CSRF-Token', ''))) {
            return new JsonResponse(['error' => 'Invalid CSRF token'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $content = trim((string) ($data['content'] ?? ''));

        $comment = new Comment();
        $comment->setContent($content);
        $comment->setEstate($estate);

        /** @var User $user */
        $user = $this->getUser();
        $comment->setEnabled($user->hasRole('ROLE_ADMIN'));

        $errors = $this->validator->validate($comment);
        if (count($errors) > 0) {
            return new JsonResponse(
                ['error' => $errors->get(0)->getMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $em = $this->doctrine->getManager();
        $em->persist($comment);
        $em->flush();

        return new JsonResponse(['success' => true], Response::HTTP_CREATED);
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
docker compose exec -T web php bin/phpunit -c app/ tests/AppBundle/Controller/Api/EstateApiControllerTest.php
```

Expected: all tests PASS.

- [ ] **Step 5: Run full test suite to check for regressions**

```bash
docker compose exec -T web php bin/phpunit -c app/
```

Expected: all 49 tests + new tests PASS.

- [ ] **Step 6: Commit**

```bash
git add src/AppBundle/Controller/Api/EstateApiController.php tests/AppBundle/Controller/Api/EstateApiControllerTest.php
git commit -m "Added EstateApiController for favorite and comment JSON endpoints"
```

---

## Task 2: Backend — Admin Pending Count API Controller

**Files:**
- Create: `src/AppBundle/Controller/Api/AdminApiController.php`
- Test: `tests/AppBundle/Controller/Api/AdminApiControllerTest.php`

- [ ] **Step 1: Write the failing tests**

Create `tests/AppBundle/Controller/Api/AdminApiControllerTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\AppBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminApiControllerTest extends WebTestCase
{
    public function testPendingCountRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/admin/comments/pending-count');
        $this->assertResponseRedirects();
    }

    public function testPendingCountRequiresManagerRole(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(\AppBundle\Entity\User::class)->findOneBy(['username' => 'user']);
        $client->loginUser($user);

        $client->request('GET', '/api/admin/comments/pending-count');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testPendingCountReturnsCount(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();
        $manager = $em->getRepository(\AppBundle\Entity\User::class)->findOneBy(['username' => 'admin']);
        $client->loginUser($manager);

        $client->request('GET', '/api/admin/comments/pending-count');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('count', $data);
        $this->assertIsInt($data['count']);
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
docker compose exec -T web php bin/phpunit -c app/ tests/AppBundle/Controller/Api/AdminApiControllerTest.php
```

Expected: errors (class not found / 404 responses).

- [ ] **Step 3: Create the controller**

Create `src/AppBundle/Controller/Api/AdminApiController.php`:

```php
<?php

declare(strict_types=1);

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Comment;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MANAGER')]
#[Route('/api/admin')]
class AdminApiController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine) {}

    #[Route('/comments/pending-count', name: 'api_admin_comments_pending_count', methods: ['GET'])]
    public function pendingCount(): JsonResponse
    {
        $comments = $this->doctrine
            ->getRepository(Comment::class)
            ->getDisabledComments();

        return new JsonResponse(['count' => count($comments)]);
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
docker compose exec -T web php bin/phpunit -c app/ tests/AppBundle/Controller/Api/AdminApiControllerTest.php
```

Expected: all 3 tests PASS.

- [ ] **Step 5: Run full test suite**

```bash
docker compose exec -T web php bin/phpunit -c app/
```

Expected: all tests PASS.

- [ ] **Step 6: Commit**

```bash
git add src/AppBundle/Controller/Api/AdminApiController.php tests/AppBundle/Controller/Api/AdminApiControllerTest.php
git commit -m "Added AdminApiController for pending comment count JSON endpoint"
```

---

## Task 3: LiveSearch Component (+ remove HelloIsland)

**Files:**
- Create: `assets/site/components/LiveSearch.tsx`
- Modify: `assets/site/entry.tsx`
- Modify: `templates/site/base_site.html.twig`
- Delete: `assets/site/components/HelloIsland.tsx`

- [ ] **Step 1: Update the Twig template**

In `templates/site/base_site.html.twig`, replace the livesearch form block (lines 38–51) with a mount div. Also remove the `livesearch.js` script reference.

Replace this block:
```twig
<form action="{{ path('livesearch') }}" method="post" class="form-inline">
    <div class="input-group">
        <input type="text" class="form-control" name="slug"
               onkeyup="showResult(this.value)"
               placeholder={{ 'common.search.placeholder'|trans }}>

        <div class="input-group-btn">
            <button type="submit" class="btn btn-default"><i
                        class="glyphicon glyphicon-search"></i>
            </button>
        </div>
    </div>
    <div id="livesearch"></div>
</form>
```

With:
```twig
<div id="react-live-search"
     data-placeholder="{{ 'common.search.placeholder'|trans }}"
     data-locale="{{ app.request.locale }}"></div>
```

Also in the `{% block javascripts %}` section of `base_site.html.twig`, remove:
```twig
<script src="{{ asset('js/livesearch.js') }}"></script>
```

- [ ] **Step 2: Create the LiveSearch component**

Create `assets/site/components/LiveSearch.tsx`:

```tsx
import React, { useEffect, useRef, useState } from 'react';

interface EstateResult {
    slug: string;
    title: string;
}

interface Props {
    placeholder: string;
    locale: string;
}

export default function LiveSearch({ placeholder, locale }: Props) {
    const [query, setQuery] = useState('');
    const [results, setResults] = useState<EstateResult[]>([]);
    const [allEstates, setAllEstates] = useState<EstateResult[]>([]);
    const [open, setOpen] = useState(false);
    const wrapperRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        fetch('/livesearch')
            .then(r => r.json())
            .then((data: Record<string, string>) => {
                const estates = Object.entries(data).map(([slug, title]) => ({ slug, title }));
                setAllEstates(estates);
            })
            .catch(() => {});
    }, []);

    useEffect(() => {
        if (query.trim().length === 0) {
            setResults([]);
            setOpen(false);
            return;
        }
        const q = query.toLowerCase();
        const filtered = allEstates.filter(e => e.title.toLowerCase().includes(q)).slice(0, 10);
        setResults(filtered);
        setOpen(filtered.length > 0);
    }, [query, allEstates]);

    useEffect(() => {
        function handleClick(e: MouseEvent) {
            if (wrapperRef.current && !wrapperRef.current.contains(e.target as Node)) {
                setOpen(false);
            }
        }
        document.addEventListener('mousedown', handleClick);
        return () => document.removeEventListener('mousedown', handleClick);
    }, []);

    return (
        <div ref={wrapperRef} style={{ position: 'relative' }} className="form-inline">
            <div className="input-group">
                <input
                    type="text"
                    className="form-control"
                    placeholder={placeholder}
                    value={query}
                    onChange={e => setQuery(e.target.value)}
                />
                <div className="input-group-btn">
                    <button type="button" className="btn btn-default">
                        <i className="glyphicon glyphicon-search" />
                    </button>
                </div>
            </div>
            {open && (
                <ul
                    className="dropdown-menu"
                    style={{ display: 'block', width: '100%', top: '100%', left: 0 }}
                >
                    {results.map(estate => (
                        <li key={estate.slug}>
                            <a href={`/${locale}/show_estate/${estate.slug}`}>{estate.title}</a>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
```

- [ ] **Step 3: Rewrite entry.tsx — mount LiveSearch, remove HelloIsland**

Replace `assets/site/entry.tsx` entirely:

```tsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import LiveSearch from './components/LiveSearch';

function mountIsland<P extends object>(
    id: string,
    Component: React.ComponentType<P>,
    props: P,
): void {
    const el = document.getElementById(id);
    if (el) {
        ReactDOM.createRoot(el).render(
            <React.StrictMode>
                <Component {...props} />
            </React.StrictMode>,
        );
    }
}

// LiveSearch
const lsEl = document.getElementById('react-live-search');
if (lsEl) {
    mountIsland('react-live-search', LiveSearch, {
        placeholder: lsEl.dataset.placeholder ?? '',
        locale: lsEl.dataset.locale ?? 'uk',
    });
}
```

- [ ] **Step 4: Delete HelloIsland**

```bash
rm assets/site/components/HelloIsland.tsx
```

Also remove the `<div id="react-hello-island">` from any Twig template if it was added. Check:

```bash
grep -r "react-hello-island" templates/
```

If found, remove that div from the template.

- [ ] **Step 5: Build to verify no TypeScript errors**

```bash
npm run build 2>&1 | tail -20
```

Expected: build completes with no errors.

- [ ] **Step 6: Run full test suite**

```bash
docker compose exec -T web php bin/phpunit -c app/
```

Expected: all tests PASS.

- [ ] **Step 7: Commit**

```bash
git add assets/site/components/LiveSearch.tsx assets/site/entry.tsx templates/site/base_site.html.twig
git rm assets/site/components/HelloIsland.tsx
git commit -m "Added LiveSearch React component, removed HelloIsland smoke test"
```

---

## Task 4: EstateSlideshow Component

**Files:**
- Create: `assets/site/components/EstateSlideshow.tsx`
- Modify: `assets/site/entry.tsx`
- Modify: `templates/site/show_estate.html.twig`

- [ ] **Step 1: Update the Twig template**

In `templates/site/show_estate.html.twig`, replace the pgwSlideshow block and its script:

Replace:
```twig
<ul class="pgwSlideshow">
    {% for file in estate.files %}
        <li><img alt="фото квартиры купить Черкассы" src="{{ asset(file.path) |imagine_filter('large') }} "></li>
    {% endfor %}
</ul>
```

With:
```twig
<div id="react-estate-slideshow"
     data-images="{{ estate.files|map(f => asset(f.path)|imagine_filter('large'))|json_encode }}">
</div>
```

And remove the javascripts block override in `show_estate.html.twig` (the pgwslideshow script + jQuery ready call):
```twig
{% block javascripts %}
    {{ parent() }}
    <script src="{{ asset('js/pgwslideshow.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.pgwSlideshow').pgwSlideshow();
        });
    </script>
{% endblock javascripts %}
```

Remove the entire `{% block javascripts %}` override from this template.

- [ ] **Step 2: Create the EstateSlideshow component**

Create `assets/site/components/EstateSlideshow.tsx`:

```tsx
import React, { useEffect, useState } from 'react';

interface Props {
    images: string[];
}

export default function EstateSlideshow({ images }: Props) {
    const [current, setCurrent] = useState(0);

    useEffect(() => {
        if (images.length <= 1) return;
        const timer = setInterval(() => {
            setCurrent(i => (i + 1) % images.length);
        }, 4000);
        return () => clearInterval(timer);
    }, [images.length]);

    if (images.length === 0) return null;

    return (
        <div style={{ position: 'relative', marginBottom: '20px' }}>
            <img
                src={images[current]}
                alt=""
                style={{ width: '100%', maxHeight: '400px', objectFit: 'cover' }}
            />
            {images.length > 1 && (
                <div style={{ textAlign: 'center', marginTop: '8px' }}>
                    <button
                        className="btn btn-default btn-sm"
                        onClick={() => setCurrent(i => (i - 1 + images.length) % images.length)}
                    >
                        ‹
                    </button>
                    <span style={{ margin: '0 8px' }}>{current + 1} / {images.length}</span>
                    <button
                        className="btn btn-default btn-sm"
                        onClick={() => setCurrent(i => (i + 1) % images.length)}
                    >
                        ›
                    </button>
                </div>
            )}
        </div>
    );
}
```

- [ ] **Step 3: Add EstateSlideshow to entry.tsx**

Append to `assets/site/entry.tsx`:

```tsx
import EstateSlideshow from './components/EstateSlideshow';

// EstateSlideshow
const slideshowEl = document.getElementById('react-estate-slideshow');
if (slideshowEl) {
    const images: string[] = JSON.parse(slideshowEl.dataset.images ?? '[]');
    mountIsland('react-estate-slideshow', EstateSlideshow, { images });
}
```

- [ ] **Step 4: Build and verify**

```bash
npm run build 2>&1 | tail -20
```

Expected: no errors.

- [ ] **Step 5: Run full test suite**

```bash
docker compose exec -T web php bin/phpunit -c app/
```

Expected: all tests PASS.

- [ ] **Step 6: Commit**

```bash
git add assets/site/components/EstateSlideshow.tsx assets/site/entry.tsx templates/site/show_estate.html.twig
git commit -m "Added EstateSlideshow React component, replaced pgwSlideshow jQuery plugin"
```

---

## Task 5: FavoriteButton Component

**Files:**
- Create: `assets/site/components/FavoriteButton.tsx`
- Modify: `assets/site/entry.tsx`
- Modify: `templates/site/show_estate.html.twig`

- [ ] **Step 1: Update the Twig template**

In `templates/site/show_estate.html.twig`, replace the favorite button block:

Replace:
```twig
{% if is_granted('IS_AUTHENTICATED_FULLY') %}
    {% if app.user.hasEstate(estate) %}
        <a href="{{ path('delete_estate_from_favorites', { 'estate': estate.slug, 'user': app.user.id }) }}"
           class="btn btn-info btn-sm">
            <span class="glyphicon glyphicon-star-empty"></span> {{ 'common.delete_favorites'|trans }}
        </a>
    {% else %}
        <a href="{{ path('add_estate_to_favorites', { 'estate': estate.slug, 'user': app.user.id }) }}"
           class="btn btn-info btn-sm">
            <span class="glyphicon glyphicon-star-empty"></span> {{ 'common.add_favorites'|trans }}
        </a>
    {% endif %}
{% endif %}
```

With:
```twig
{% if is_granted('IS_AUTHENTICATED_FULLY') %}
    <div id="react-favorite-button"
         data-slug="{{ estate.slug }}"
         data-favorited="{{ app.user.hasEstate(estate) ? 'true' : 'false' }}"
         data-csrf="{{ csrf_token('api_estate_favorite') }}"
         data-label-add="{{ 'common.add_favorites'|trans }}"
         data-label-remove="{{ 'common.delete_favorites'|trans }}">
    </div>
{% endif %}
```

- [ ] **Step 2: Create the FavoriteButton component**

Create `assets/site/components/FavoriteButton.tsx`:

```tsx
import React, { useState } from 'react';

interface Props {
    slug: string;
    favorited: boolean;
    csrf: string;
    labelAdd: string;
    labelRemove: string;
}

export default function FavoriteButton({ slug, favorited: initialFavorited, csrf, labelAdd, labelRemove }: Props) {
    const [favorited, setFavorited] = useState(initialFavorited);
    const [loading, setLoading] = useState(false);

    async function toggle() {
        setLoading(true);
        try {
            const method = favorited ? 'DELETE' : 'POST';
            const response = await fetch(`/api/estate/${slug}/favorite`, {
                method,
                headers: { 'X-CSRF-Token': csrf },
            });
            if (response.ok) {
                const data = await response.json();
                setFavorited(data.favorited);
            }
        } finally {
            setLoading(false);
        }
    }

    return (
        <button
            type="button"
            className="btn btn-info btn-sm"
            onClick={toggle}
            disabled={loading}
        >
            <span className="glyphicon glyphicon-star-empty" />
            {' '}
            {favorited ? labelRemove : labelAdd}
        </button>
    );
}
```

- [ ] **Step 3: Add FavoriteButton to entry.tsx**

Append to `assets/site/entry.tsx`:

```tsx
import FavoriteButton from './components/FavoriteButton';

// FavoriteButton
const fbEl = document.getElementById('react-favorite-button');
if (fbEl) {
    mountIsland('react-favorite-button', FavoriteButton, {
        slug: fbEl.dataset.slug ?? '',
        favorited: fbEl.dataset.favorited === 'true',
        csrf: fbEl.dataset.csrf ?? '',
        labelAdd: fbEl.dataset.labelAdd ?? '',
        labelRemove: fbEl.dataset.labelRemove ?? '',
    });
}
```

- [ ] **Step 4: Build and verify**

```bash
npm run build 2>&1 | tail -20
```

Expected: no errors.

- [ ] **Step 5: Run full test suite**

```bash
docker compose exec -T web php bin/phpunit -c app/
```

Expected: all tests PASS.

- [ ] **Step 6: Commit**

```bash
git add assets/site/components/FavoriteButton.tsx assets/site/entry.tsx templates/site/show_estate.html.twig
git commit -m "Added FavoriteButton React component with AJAX add/remove via API endpoint"
```

---

## Task 6: CommentForm Component

**Files:**
- Create: `assets/site/components/CommentForm.tsx`
- Modify: `assets/site/entry.tsx`
- Modify: `templates/site/show_estate.html.twig`

- [ ] **Step 1: Update the Twig template**

In `templates/site/show_estate.html.twig`, replace the comment form sub-render block.

Replace:
```twig
{% if is_granted('IS_AUTHENTICATED_FULLY') %}
    <div class="well">
        {{ render(controller('AppBundle\\Controller\\SiteController::commentNewAction', { 'slug': estate.slug })) }}
    </div>
{% else %} <p>{{ 'site.sing_in_please'|trans }}</p>
    <hr>
{% endif %}
{% if app.session.flashBag.has('success') %}
    <div class="alert alert-success">
        {% for msg in app.session.flashBag.get('success') %}
            {{ msg|trans }}
        {% endfor %}
    </div>
{% endif %}
```

With:
```twig
{% if is_granted('IS_AUTHENTICATED_FULLY') %}
    <div class="well">
        <div id="react-comment-form"
             data-slug="{{ estate.slug }}"
             data-csrf="{{ csrf_token('api_estate_comment') }}"
             data-placeholder="{{ 'site.comment_placeholder'|trans }}"
             data-submit="{{ 'site.comment_submit'|trans }}">
        </div>
    </div>
{% else %}
    <p>{{ 'site.sing_in_please'|trans }}</p>
    <hr>
{% endif %}
```

- [ ] **Step 2: Add translation keys**

In `translations/messages.uk.yml` (and `messages.en.yml` if it exists), add:

```yaml
site:
    comment_placeholder: 'Ваш коментар...'
    comment_submit: 'Надіслати'
```

- [ ] **Step 3: Create the CommentForm component**

Create `assets/site/components/CommentForm.tsx`:

```tsx
import React, { useState } from 'react';

interface Props {
    slug: string;
    csrf: string;
    placeholder: string;
    submit: string;
}

export default function CommentForm({ slug, csrf, placeholder, submit }: Props) {
    const [content, setContent] = useState('');
    const [status, setStatus] = useState<'idle' | 'sending' | 'success' | 'error'>('idle');
    const [errorMsg, setErrorMsg] = useState('');

    async function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        if (!content.trim()) return;

        setStatus('sending');
        try {
            const response = await fetch(`/api/estate/${slug}/comment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrf,
                },
                body: JSON.stringify({ content }),
            });

            if (response.status === 201) {
                setStatus('success');
                setContent('');
            } else {
                const data = await response.json();
                setErrorMsg(data.error ?? 'Error');
                setStatus('error');
            }
        } catch {
            setStatus('error');
            setErrorMsg('Network error');
        }
    }

    return (
        <form onSubmit={handleSubmit}>
            <div className="form-group">
                <textarea
                    className="form-control"
                    rows={4}
                    placeholder={placeholder}
                    value={content}
                    onChange={e => setContent(e.target.value)}
                    disabled={status === 'sending'}
                />
            </div>
            {status === 'success' && (
                <div className="alert alert-success">Comment submitted for review.</div>
            )}
            {status === 'error' && (
                <div className="alert alert-danger">{errorMsg}</div>
            )}
            <button
                type="submit"
                className="btn btn-primary"
                disabled={status === 'sending' || !content.trim()}
            >
                {status === 'sending' ? '...' : submit}
            </button>
        </form>
    );
}
```

- [ ] **Step 4: Add CommentForm to entry.tsx**

Append to `assets/site/entry.tsx`:

```tsx
import CommentForm from './components/CommentForm';

// CommentForm
const cfEl = document.getElementById('react-comment-form');
if (cfEl) {
    mountIsland('react-comment-form', CommentForm, {
        slug: cfEl.dataset.slug ?? '',
        csrf: cfEl.dataset.csrf ?? '',
        placeholder: cfEl.dataset.placeholder ?? '',
        submit: cfEl.dataset.submit ?? 'Submit',
    });
}
```

- [ ] **Step 5: Build and verify**

```bash
npm run build 2>&1 | tail -20
```

Expected: no errors.

- [ ] **Step 6: Run full test suite**

```bash
docker compose exec -T web php bin/phpunit -c app/
```

Expected: all tests PASS.

- [ ] **Step 7: Commit**

```bash
git add assets/site/components/CommentForm.tsx assets/site/entry.tsx templates/site/show_estate.html.twig translations/
git commit -m "Added CommentForm React component with AJAX submit via API endpoint"
```

---

## Task 7: AdminSidebar Component

**Files:**
- Create: `assets/admin/components/AdminSidebar.tsx`
- Modify: `assets/admin/entry.tsx`
- Modify: `templates/admin/base_admin.html.twig`

- [ ] **Step 1: Update the Twig template**

In `templates/admin/base_admin.html.twig`, replace the sidebar `<ul>` block with a mount div. Pass the nav items as JSON so the component stays data-driven.

Replace the entire `{% block sidebar %}` contents:

```twig
{% block sidebar %}
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <div id="react-admin-sidebar" data-items="{{ [
                { 'route': path('admin_estates'),    'label': 'Об\'єкти' },
                { 'route': path('admin_districts'),  'label': 'Райони' },
                { 'route': path('admin_users'),      'label': 'Користувачі' },
                { 'route': path('admin_categories'), 'label': 'Категорії' },
                { 'route': path('admin_comments'),   'label': 'Коментарі' },
                { 'route': path('admin_items'),      'label': 'Меню' }
            ]|json_encode }}"></div>
        </div>
    </div>
{% endblock sidebar %}
```

- [ ] **Step 2: Create the AdminSidebar component**

Create `assets/admin/components/AdminSidebar.tsx`:

```tsx
import React from 'react';

interface NavItem {
    route: string;
    label: string;
}

interface Props {
    items: NavItem[];
}

export default function AdminSidebar({ items }: Props) {
    const currentPath = window.location.pathname;

    return (
        <ul className="nav" id="side-menu">
            {items.map(item => (
                <li key={item.route} className={currentPath.startsWith(item.route) ? 'active' : ''}>
                    <a href={item.route}>
                        <i className="fa fa-edit fa-fw" /> {item.label}
                    </a>
                </li>
            ))}
        </ul>
    );
}
```

- [ ] **Step 3: Rewrite admin entry.tsx — add AdminSidebar**

Replace `assets/admin/entry.tsx` entirely:

```tsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import AdminSidebar from './components/AdminSidebar';

function mountIsland<P extends object>(
    id: string,
    Component: React.ComponentType<P>,
    props: P,
): void {
    const el = document.getElementById(id);
    if (el) {
        ReactDOM.createRoot(el).render(
            <React.StrictMode>
                <Component {...props} />
            </React.StrictMode>,
        );
    }
}

// AdminSidebar
const sidebarEl = document.getElementById('react-admin-sidebar');
if (sidebarEl) {
    const items = JSON.parse(sidebarEl.dataset.items ?? '[]');
    mountIsland('react-admin-sidebar', AdminSidebar, { items });
}
```

- [ ] **Step 4: Build and verify**

```bash
npm run build 2>&1 | tail -20
```

Expected: no errors.

- [ ] **Step 5: Run full test suite**

```bash
docker compose exec -T web php bin/phpunit -c app/
```

Expected: all tests PASS.

- [ ] **Step 6: Commit**

```bash
git add assets/admin/components/AdminSidebar.tsx assets/admin/entry.tsx templates/admin/base_admin.html.twig
git commit -m "Added AdminSidebar React component with active-state highlighting"
```

---

## Task 8: CommentBadge Component

**Files:**
- Create: `assets/admin/components/CommentBadge.tsx`
- Modify: `assets/admin/entry.tsx`
- Modify: `templates/admin/base_admin.html.twig`

- [ ] **Step 1: Update the Twig template**

In `templates/admin/base_admin.html.twig`, replace the comment badge sub-render.

In the `data-items` JSON passed to AdminSidebar (from Task 7), the Comments item doesn't have a badge. We need a separate mount point for the badge. The cleanest approach is to add the badge as a separate island next to the AdminSidebar, and have the sidebar link include a placeholder where the badge will appear.

Update the Comments nav item in `data-items` to include a badge placeholder:

In `templates/admin/base_admin.html.twig`, after the `react-admin-sidebar` div, add:

```twig
<div id="react-comment-badge" style="display:none"></div>
```

And update the `data-items` JSON to mark the comments item so the sidebar component knows to show the badge:

```twig
{ 'route': path('admin_comments'), 'label': 'Коментарі', 'badge': true },
```

Then update `AdminSidebar.tsx` to render a `<span id="react-comment-badge-slot">` inside that nav item. The `CommentBadge` will mount into that slot.

Actually, a simpler approach: pass the badge count via polling and render it in-line in the sidebar. Let's keep it simple — CommentBadge is a standalone island that replaces the old `render(controller(...))` output. We place the mount div inside the Comments nav item in the sidebar template.

Since the sidebar is now a React component (Task 7), the badge must be wired through it. The simplest solution: CommentBadge is a child component rendered inside AdminSidebar for the Comments item.

Revise the plan: pass `badgeRoute` in the nav item config, and AdminSidebar renders `<CommentBadge />` inline for items that have it.

Update `templates/admin/base_admin.html.twig` — change the Comments item in `data-items`:

```twig
{ 'route': path('admin_comments'), 'label': 'Коментарі', 'badgeUrl': '/api/admin/comments/pending-count' },
```

- [ ] **Step 2: Create the CommentBadge component**

Create `assets/admin/components/CommentBadge.tsx`:

```tsx
import React, { useEffect, useState } from 'react';

interface Props {
    url: string;
}

export default function CommentBadge({ url }: Props) {
    const [count, setCount] = useState<number | null>(null);

    useEffect(() => {
        function fetchCount() {
            fetch(url)
                .then(r => r.json())
                .then(data => setCount(data.count))
                .catch(() => {});
        }

        fetchCount();
        const timer = setInterval(fetchCount, 30_000);
        return () => clearInterval(timer);
    }, [url]);

    if (count === null || count === 0) return null;

    return <span className="badge">{count}</span>;
}
```

- [ ] **Step 3: Update AdminSidebar to render CommentBadge**

Update `assets/admin/components/AdminSidebar.tsx`:

```tsx
import React from 'react';
import CommentBadge from './CommentBadge';

interface NavItem {
    route: string;
    label: string;
    badgeUrl?: string;
}

interface Props {
    items: NavItem[];
}

export default function AdminSidebar({ items }: Props) {
    const currentPath = window.location.pathname;

    return (
        <ul className="nav" id="side-menu">
            {items.map(item => (
                <li key={item.route} className={currentPath.startsWith(item.route) ? 'active' : ''}>
                    <a href={item.route}>
                        <i className="fa fa-edit fa-fw" /> {item.label}
                        {item.badgeUrl && <CommentBadge url={item.badgeUrl} />}
                    </a>
                </li>
            ))}
        </ul>
    );
}
```

- [ ] **Step 4: Update the Twig data-items to include badgeUrl**

In `templates/admin/base_admin.html.twig`, update the Comments entry in `data-items`:

```twig
{ 'route': path('admin_comments'), 'label': 'Коментарі', 'badgeUrl': '/api/admin/comments/pending-count' },
```

Also remove the now-unused `render(controller(...))` call for `countDisablesCommentsAction` if it still exists in the template.

- [ ] **Step 5: Build and verify**

```bash
npm run build 2>&1 | tail -20
```

Expected: no errors.

- [ ] **Step 6: Run full test suite**

```bash
docker compose exec -T web php bin/phpunit -c app/
```

Expected: all tests PASS.

- [ ] **Step 7: Commit**

```bash
git add assets/admin/components/CommentBadge.tsx assets/admin/components/AdminSidebar.tsx assets/admin/entry.tsx templates/admin/base_admin.html.twig
git commit -m "Added CommentBadge component polling pending count; integrated into AdminSidebar"
```

---

## Task 9: DataTable Component (Admin Estates)

**Files:**
- Create: `assets/admin/components/DataTable.tsx`
- Modify: `assets/admin/entry.tsx`
- Modify: `templates/admin/estate/estates.html.twig`

- [ ] **Step 1: Update the Twig template**

In `templates/admin/estate/estates.html.twig`, add a JSON data script and a mount div. Keep the existing table as a `<noscript>` fallback.

Replace the entire `{% block panel %}` content:

```twig
{% block panel %}
    <h1>Об'єкти нерухомості</h1>
    <a href="{{ path('admin_estate_new') }}" class="btn btn-lg btn-block btn-success">
        <i class="fa fa-plus"></i> Додати новий
    </a>

    <script type="application/json" id="react-estates-data">
    {{ pagination|map(estate => {
        'title':     estate.title,
        'slug':      estate.slug,
        'category':  estate.category.title,
        'price':     estate.price,
        'createdAt': estate.createdAt|date('d.m.Y'),
        'district':  estate.district.title,
        'exclusive': estate.exclusive,
        'showUrl':   path('admin_estate_show', { 'slug': estate.slug }),
        'editUrl':   path('admin_estate_edit', { 'slug': estate.slug })
    })|json_encode }}
    </script>

    <div id="react-admin-data-table"></div>

    <noscript>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Назва</th><th>Категорія</th><th>Ціна</th>
                <th>Створено</th><th>Район</th><th>Ексклюзив</th><th>Дії</th>
            </tr>
            </thead>
            <tbody>
            {% for estate in pagination %}
                <tr>
                    <td>{{ estate.title }}</td>
                    <td>{{ estate.category.title|trans }}</td>
                    <td>{{ estate.price }}</td>
                    <td>{{ estate.createdAt|date("d F Y H:i:s") }}</td>
                    <td>{{ estate.district.title }}</td>
                    <td>{{ estate.exclusive ? 'Так' : '-' }}</td>
                    <td>
                        <a href="{{ path('admin_estate_show', { 'slug': estate.slug }) }}" class="btn btn-sm btn-default">Показати</a>
                        <a href="{{ path('admin_estate_edit', { 'slug': estate.slug }) }}" class="btn btn-sm btn-default"><i class="fa fa-edit"></i> Змінити</a>
                    </td>
                </tr>
            {% endfor %}
            </tbody>
        </table>
    </noscript>

    <div class="navigation">
        {{ knp_pagination_render(pagination) }}
    </div>
{% endblock panel %}
```

- [ ] **Step 2: Create the DataTable component**

Create `assets/admin/components/DataTable.tsx`:

```tsx
import React, { useState } from 'react';

interface EstateRow {
    title: string;
    slug: string;
    category: string;
    price: number | null;
    createdAt: string;
    district: string;
    exclusive: boolean;
    showUrl: string;
    editUrl: string;
}

type SortKey = keyof Pick<EstateRow, 'title' | 'category' | 'price' | 'createdAt' | 'district'>;

interface Props {
    rows: EstateRow[];
}

export default function DataTable({ rows }: Props) {
    const [sortKey, setSortKey] = useState<SortKey>('createdAt');
    const [sortAsc, setSortAsc] = useState(false);

    function toggleSort(key: SortKey) {
        if (sortKey === key) {
            setSortAsc(a => !a);
        } else {
            setSortKey(key);
            setSortAsc(true);
        }
    }

    const sorted = [...rows].sort((a, b) => {
        const av = a[sortKey] ?? '';
        const bv = b[sortKey] ?? '';
        const cmp = String(av).localeCompare(String(bv), undefined, { numeric: true });
        return sortAsc ? cmp : -cmp;
    });

    function header(key: SortKey, label: string) {
        const active = sortKey === key;
        return (
            <th
                style={{ cursor: 'pointer', userSelect: 'none' }}
                onClick={() => toggleSort(key)}
            >
                {label} {active ? (sortAsc ? '▲' : '▼') : ''}
            </th>
        );
    }

    return (
        <table className="table table-striped">
            <thead>
                <tr>
                    {header('title', 'Назва')}
                    {header('category', 'Категорія')}
                    {header('price', 'Ціна')}
                    {header('createdAt', 'Створено')}
                    {header('district', 'Район')}
                    <th>Ексклюзив</th>
                    <th>Дії</th>
                </tr>
            </thead>
            <tbody>
                {sorted.map(row => (
                    <tr key={row.slug}>
                        <td>{row.title}</td>
                        <td>{row.category}</td>
                        <td>{row.price ?? '—'}</td>
                        <td>{row.createdAt}</td>
                        <td>{row.district}</td>
                        <td>{row.exclusive ? 'Так' : '—'}</td>
                        <td>
                            <a href={row.showUrl} className="btn btn-sm btn-default">Показати</a>
                            {' '}
                            <a href={row.editUrl} className="btn btn-sm btn-default">
                                <i className="fa fa-edit" /> Змінити
                            </a>
                        </td>
                    </tr>
                ))}
            </tbody>
        </table>
    );
}
```

- [ ] **Step 3: Add DataTable to admin entry.tsx**

Append to `assets/admin/entry.tsx`:

```tsx
import DataTable from './components/DataTable';

// DataTable
const dataScript = document.getElementById('react-estates-data');
if (dataScript) {
    const rows = JSON.parse(dataScript.textContent ?? '[]');
    mountIsland('react-admin-data-table', DataTable, { rows });
}
```

- [ ] **Step 4: Build and verify**

```bash
npm run build 2>&1 | tail -20
```

Expected: no errors.

- [ ] **Step 5: Run full test suite**

```bash
docker compose exec -T web php bin/phpunit -c app/
```

Expected: all tests PASS.

- [ ] **Step 6: Commit**

```bash
git add assets/admin/components/DataTable.tsx assets/admin/entry.tsx templates/admin/estate/estates.html.twig
git commit -m "Added DataTable React component with client-side sort for admin estates list"
```

---

## Self-Review

### Spec Coverage

| Spec requirement | Task |
|-----------------|------|
| LiveSearch replaces livesearch.js | Task 3 |
| EstateSlideshow replaces pgwSlideshow | Task 4 |
| FavoriteButton — add/remove via AJAX | Tasks 1 + 5 |
| CommentForm — React form with AJAX submit | Tasks 1 + 6 |
| AdminSidebar — replaces MetisMenu/sb-admin-2 | Task 7 |
| CommentBadge — polls pending count | Tasks 2 + 8 |
| DataTable — client-side sort | Task 9 |
| All 49 existing PHP tests pass throughout | Each task step 5/6 |
| New API endpoints covered by tests | Tasks 1 + 2 |

### Spec Discrepancies (documented)

The design spec notes these as "no backend changes needed" but the implementation requires new endpoints:
- **FavoriteButton**: current code uses redirect links (not AJAX); new `POST/DELETE /api/estate/{slug}/favorite` endpoints are required
- **CommentForm**: current code uses a Symfony form with server redirect; new `POST /api/estate/{slug}/comment` is required
- **CommentBadge**: new `GET /api/admin/comments/pending-count` is required
- **DataTable**: spec says "replaces DataTables jQuery plugin" but no such plugin existed; this is a new client-side sort enhancement

### Type Consistency Check

- `EstateRow` defined in Task 9, only used in `DataTable.tsx` — consistent
- `NavItem` defined in Task 7, used in `AdminSidebar.tsx` — `badgeUrl` added in Task 8, consistent
- `mountIsland` helper defined identically in both `site/entry.tsx` and `admin/entry.tsx` — by design (no shared module for now)
- CSRF token IDs: `'api_estate_favorite'` (Tasks 1+5), `'api_estate_comment'` (Tasks 1+6) — consistent across PHP and TSX
