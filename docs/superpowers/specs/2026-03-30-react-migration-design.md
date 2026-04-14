
# React Migration Design — Rainbow

**Date:** 2026-03-30
**Project:** Rainbow (Symfony 7.4 real estate app)
**Status:** Approved

---

## 1. Goal

Incrementally migrate the Rainbow frontend from a legacy Gulp + LESS + Bootstrap 3 + jQuery stack to React 18 + TypeScript + Tailwind CSS + shadcn/ui, without disrupting the Symfony backend or breaking existing functionality during the transition.

---

## 2. Integration Approach — Islands

React components are mounted as **islands** inside existing Twig page shells. Symfony continues to own routing, authentication, and HTML page rendering. Twig templates remain as the outer page structure; individual interactive sections are replaced with React components mounted into dedicated DOM nodes.

```
GET /estate/123
  → Symfony renders Twig shell (HTML)
  → Browser loads Encore JS bundles
  → React components mount into #react-* nodes
  → Each component fetches its own data via AJAX (GET /api/estate/123)
  → Symfony returns JsonResponse
```

This approach allows migration one component or section at a time, with no "big bang" cutover.

---

## 3. Tech Stack

### Backend (unchanged)
- Symfony 7.4 + PHP 8.4
- Twig (page shells, progressively hollowed out)
- Doctrine ORM, Symfony Security, existing routes
- **New:** JSON API actions added alongside existing controller actions (`/api/*` prefix)

### Frontend (new)
| Concern | Old | New |
|---------|-----|-----|
| Build tool | Gulp 3 + Bower | Symfony Webpack Encore |
| CSS preprocessor | LESS | Tailwind CSS + PostCSS |
| UI framework | Bootstrap 3 | shadcn/ui + Tailwind |
| JavaScript | jQuery + inline scripts | React 18 + TypeScript |
| Package manager | Bower + npm | npm only |

---

## 4. Asset Structure

```
assets/                        ← replaces web-src/
  app.ts                       ← main entry (shared: Tailwind base, global types)
  site/
    entry.tsx                  ← public site entry point
    components/
      LiveSearch.tsx
      EstateSlideshow.tsx
      FavoriteButton.tsx
      CommentForm.tsx
  admin/
    entry.tsx                  ← admin panel entry point
    components/
      AdminSidebar.tsx
      DataTable.tsx
      CommentBadge.tsx
      estates/
      comments/
      districts/
      categories/
      users/
      menu-items/
  components/ui/               ← shadcn/ui generated components
```

Encore entry points:
- `app` — shared Tailwind base CSS
- `site` — public site React bundle
- `admin` — admin panel React bundle

---

## 5. Data Fetching Convention

Each React component is responsible for fetching its own data on mount. Symfony exposes JSON API endpoints under the `/api/` prefix.

**Mounting pattern (Twig side):**
```twig
<div id="react-live-search" data-locale="{{ app.request.locale }}"></div>
{{ encore_entry_script_tags('site') }}
```

**Mounting pattern (React side):**
```tsx
const el = document.getElementById('react-live-search');
if (el) {
  ReactDOM.createRoot(el).render(<LiveSearch />);
}
```

**API endpoints** follow REST conventions:
- `GET /api/estates` — paginated estate list
- `GET /api/estates/{id}` — single estate
- `GET /api/search?q=...` — live search results
- `GET /api/admin/estates` — admin estate list
- `POST /api/admin/estates` — create estate
- etc.

All API responses return `application/json`. Symfony security applies to `/api/admin/*` routes (requires `ROLE_ADMIN`).

---

## 6. Migration Phases

### Phase 0 — Infrastructure (~1–2 days)
- Install `symfony/webpack-encore-bundle`
- Configure `webpack.config.js` with React + TypeScript support
- Install and configure Tailwind CSS + PostCSS
- Install shadcn/ui, initialise component registry
- Create `assets/` directory structure
- Update Twig base templates: replace hardcoded `<script>`/`<link>` tags with `encore_entry_script_tags()` / `encore_entry_link_tags()`
- Keep existing compiled `web/css/app.css` and `web/js/app.js` temporarily (removed in Phase 4)
- Add `npm run build` to CI pipeline
- Add `.superpowers/` to `.gitignore`

### Phase 1 — Interactive JS → React (~1 week)
Replace existing client-side JS with React components. No backend changes needed — these are already client-side or use existing AJAX endpoints.

**Public site:**
- `LiveSearch` — replaces `livesearch.js` (XMLHttpRequest → fetch, same `/livesearch` endpoint)
- `EstateSlideshow` — replaces `pgwslideshow.js` (874-line jQuery plugin)
- `FavoriteButton` — replaces inline AJAX for add/remove favorites
- `CommentForm` — replaces server-rendered form partial with React-controlled form

**Admin panel:**
- `AdminSidebar` — replaces MetisMenu + `sb-admin-2.js`
- `DataTable` — replaces DataTables jQuery plugin (shadcn/ui Table + client-side sort/filter)
- `CommentBadge` — live pending comment count (polls `/api/admin/comments/pending-count`)

### Phase 2 — Full Admin Sections (~2–3 weeks)
Admin CRUD pages become thin Twig shells. Each section gets React UI + JSON API endpoints.

Order (simplest → most complex):
1. Districts (simple CRUD, no relations)
2. Comments (moderation queue, approve/delete)
3. Users (list, lock/unlock, role assignment)
4. Menu Items (CRUD)
5. Estates (most complex: photos, categories, districts, main photo selection)
6. Categories (tree view with reorder — most complex admin feature)

Each section: add `/api/admin/{resource}` endpoints → build React list + form components → replace Twig template with shell div → test.

### Phase 3 — Full Public Site Sections (~2–3 weeks)
Public Twig templates become shells. Legacy Bootstrap 3 CSS fully replaced.

Order:
1. Auth pages (login, register, reset password — no data fetching, mostly forms)
2. Search results (filters + paginated list)
3. Estate listing (homepage + category pages — paginated grid)
4. Estate detail (gallery, info panel, comments, PDF button)

### Phase 4 — Cleanup (~2–3 days)
- Remove `web-src/`, `gulpfile.js`, `bower.json`, `bower_components/`
- Remove Bootstrap 3, jQuery, MetisMenu, Morris.js, Flot from `package.json` / Bower
- Remove compiled `web/css/app.css` and `web/js/app.js`
- Audit Twig templates: no inline `<script>` blocks remaining
- Remove `pgwslideshow.js`, `sb-admin-2.js`, `scripts.js`, `livesearch.js`

---

## 7. API Design Conventions

- Prefix: `/api/` (public), `/api/admin/` (admin, requires `ROLE_ADMIN`)
- Format: JSON, `Content-Type: application/json`
- Pagination: `{ data: [...], total: int, page: int, perPage: int }`
- Errors: `{ error: string, code: int }`
- CSRF: admin mutation endpoints use Symfony's CSRF token (passed as `X-CSRF-Token` header)
- Auth: Symfony session cookie (no JWT — the app already uses session-based auth)

---

## 8. Constraints

- React work happens on a separate `feature/react-frontend` branch cut from `dev` (currently Symfony 7.4).
- All existing PHPUnit tests must continue to pass throughout the migration.
- Social OAuth (Facebook, Google, VK, Twitter) must remain functional — the HWI OAuth bundle stays.
- PDF generation (KnpSnappy) stays server-side — `pdf.html.twig` is excluded from the React migration.
- Image handling (LiipImagine) stays server-side — `<img>` tags in React components use Twig-generated URLs passed as props or returned from the API.

---

## 9. Out of Scope

- Server-side rendering (SSR) of React components — not needed, SEO is handled by the Symfony-rendered HTML shell
- React Router — Symfony owns all routing, no client-side routing
- State management library (Redux/Zustand) — component-local state is sufficient for islands
- PWA / offline support
- PDF template (`pdf.html.twig`) migration