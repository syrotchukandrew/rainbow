# Warm Editorial Redesign — Design Spec

## Goal

Restyle the public site with a warm editorial palette (espresso nav, amber accent, parchment background) and apply the same amber accent to the admin panel.

## Scope

### Site (`base_site.html.twig` + `globals.css`)

**What changes:**
- CSS palette tokens updated (see below)
- Nav bar restyled: espresso dark background, light text, existing links/icons kept
- Separate `<header>` block (logo image + search form panel) removed entirely
- Page background: warm parchment
- Sidebar: stone background with warm borders
- Property cards: white with warm borders, amber price text
- Footer: espresso dark to match nav
- Headings: Georgia serif via Tailwind config

**What does NOT change:**
- Nav links, locale switcher, search dropdown, account dropdown — structure stays identical, only colours change
- Sidebar content and category menu — structure unchanged
- Any React components — only their container colours change via CSS variables

### Admin (`globals.css` or admin-specific CSS)

- Update `--primary` CSS variable to amber `#c17c3c` — no other changes

---

## Colour Palette

| Token | Value | Usage |
|-------|-------|-------|
| `--primary` | `#c17c3c` | Buttons, active states, accent text |
| `--primary-foreground` | `#ffffff` | Text on primary buttons |
| Espresso | `#2c2420` | Nav background, footer background |
| Dark Walnut | `#5c3d2e` | Nav hover, gradient |
| Amber | `#c17c3c` | Accent, prices, CTA |
| Honey | `#e8c47a` | Hover states on dark backgrounds |
| Warm Parchment | `#f7f4f0` | Page background |
| Stone | `#ede8e0` | Sidebar background |
| Warm Border | `#d9d0c4` | Card and sidebar borders |
| Body text | `#4a3b2e` | Primary body text |
| Muted text | `#8a7d6e` | Labels, meta text |
| Warm grey | `#c8bfb0` | Nav link text (on dark) |

---

## Typography

- Page headings (`h1`, `h2` in site context): Georgia serif, `font-family: Georgia, 'Times New Roman', serif`
- Added via `fontFamily.serif` in `tailwind.config.js`
- Body text: existing system-ui stack, unchanged

---

## Component Changes

### Nav (`{% block nav %}` in `base_site.html.twig`)

**Before:** `bg-white border-b border-gray-200 shadow-sm` white bar

**After:**
- Outer nav: `bg-[#2c2420]` dark espresso
- Brand link: `text-[#f5f0ea]` light cream
- Nav links (desktop menu items): `text-[#c8bfb0] hover:text-[#e8c47a]`
- Icon buttons (locale, search, account): `text-[#c8bfb0] hover:text-white`
- Dropdowns: remain white (`bg-white`) with existing border/shadow — they pop against the dark nav

### Header (`{% block header %}` in `base_site.html.twig`)

**Removed entirely.** The `<header>` block containing the logo image and search form panel is deleted. The `searchAction` sub-render and logo image are no longer rendered on every page.

**Breadcrumbs:** Currently rendered inside `{% block header %}` via `{{ wo_render_breadcrumbs() }}`. They move to the top of `{% block body %}`, just above the sidebar+main flex container:

```twig
{% block body %}
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="mb-2 text-sm text-[#8a7d6e]">{{ wo_render_breadcrumbs() }}</div>
    <div class="flex gap-6">
        ...
    </div>
</div>
{% endblock body %}
```

### Body background

`base_site.html.twig` `{% block body %}` outer div: change `bg-white` / implicit white to `bg-[#f7f4f0]`.

### Sidebar (`{% block sidebar %}` default content)

- Sidebar `<aside>` background: `bg-[#ede8e0]`
- Sidebar border: `border-r border-[#d9d0c4]`
- Category heading text: `text-[#8a7d6e]`
- Category links: `text-[#4a3b2e] hover:bg-[#dfd8cc]`
- Active category: `bg-[#c17c3c] text-white`

### Cards (estate cards)

- Card border: `border-[#ddd5c8]`
- Price text: `text-[#c17c3c] font-bold`

### Footer (`{% block footer %}`)

**Before:** `bg-gray-50 border-t border-gray-200`

**After:** `bg-[#2c2420]` dark espresso
- Footer links: `text-[#c8bfb0] hover:text-white`
- Copyright text: `text-[#8a7d6e]`

### CSS Variables (`assets/styles/globals.css`)

Update `:root` block:
```css
--primary: 29 53% 50%;        /* #c17c3c in HSL */
--primary-foreground: 0 0% 100%;
--background: 34 31% 96%;     /* #f7f4f0 warm parchment */
--foreground: 28 23% 24%;     /* #4a3b2e body text */
--muted: 33 22% 88%;          /* #ede8e0 stone */
--muted-foreground: 27 13% 48%; /* #8a7d6e muted text */
--border: 31 19% 82%;         /* #d9d0c4 warm border */
--accent: 29 53% 50%;         /* same as primary */
--accent-foreground: 0 0% 100%;
```

### `tailwind.config.js`

Add `fontFamily.serif`:
```js
fontFamily: {
  serif: ['Georgia', '"Times New Roman"', 'serif'],
}
```

---

## Admin Panel

**File:** `assets/styles/globals.css` (shared) — `--primary` is already used by the admin panel via the same CSS variable system.

The token update above (`--primary: 32 62% 49%`) applies to both site and admin automatically. No admin template changes needed.

---

## Out of Scope

- No changes to React component logic or structure
- No changes to PHP controllers or services
- No changes to admin sidebar colour, spacing, or layout beyond the accent colour
- No mobile-specific redesign beyond what Tailwind responsive classes already provide
- No new pages or routes

---

## Testing

- `php bin/phpunit` — all 97 tests must still pass (no PHP changes)
- Visual check: homepage, category page, estate detail page, login page, reset password page
- Visual check: admin index page — verify amber accent on buttons
- Verify nav dropdowns (locale, search, account) still function and display correctly
- Verify breadcrumbs still render (they move out of the deleted header — confirm their new location)
