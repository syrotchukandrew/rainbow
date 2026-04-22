# Tailwind Layout Conversion + Phase 4 Cleanup Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace all Bootstrap 3 HTML/CSS in the public site templates with Tailwind CSS, then delete legacy bower/gulp/public JS artefacts that are no longer needed.

**Architecture:** `base_site.html.twig` overrides `stylesheets` and `javascripts` blocks from `base.html.twig` so Bootstrap CSS/JS is dropped from public pages while the admin panel (which still needs Bootstrap) is left untouched. Navbar dropdowns use CSS-only `group-hover:` Tailwind utilities; the category sidebar uses HTML `<details>/<summary>` to replace MetisMenu. The search form gets a minimal Tailwind form theme. Bootstrap CSS/JS is removed from the public site only in the final pre-cleanup task. `base.html.twig` is **not** touched — admin continues to work throughout.

**Tech Stack:** Tailwind CSS 3.x (already configured), Symfony 6.4 Twig, Webpack Encore, PHP 8.1

---

## File Map

| Action | Path | Responsibility |
|--------|------|---------------|
| Modify | `templates/site/base_site.html.twig` | Full public site shell: navbar, header, body grid, footer, CSS/JS block overrides |
| Modify | `templates/includes/menu.html.twig` | Sidebar wrapper — remove Bootstrap classes |
| Modify | `templates/includes/menu-links.html.twig` | Recursive category links — replace MetisMenu with `<details>/<summary>` |
| Create | `templates/form/tailwind_layout.html.twig` | Minimal Tailwind form theme for the header search form |
| Modify | `templates/site/search.html.twig` | Switch form theme to Tailwind |
| Modify | `templates/site/show_estate.html.twig` | Panel→card, glyphicons→SVG, media→flex, col-lg grid |
| Modify | `templates/site/index.html.twig` | Panel→card, img-responsive→`w-full`, col grid, buttons |
| Delete | `bower.json` | Legacy bower build descriptor — unused |
| Delete | `gulpfile.js` | Legacy gulp build — unused |
| Delete | `bower_components/` | Vendored Bootstrap 3 source — unused |
| Delete | `public/js/livesearch.js` | Old vanilla livesearch — replaced by React LiveSearch |
| Delete | `public/js/pgwslideshow.js` | Old slider plugin — replaced by React EstateSlideshow |
| Delete | `public/js/scripts.js` | Old site scripts — no longer loaded anywhere |

> **Note:** `public/css/app.css`, `public/js/app.js`, `public/js/sb-admin-2.js`, and `base.html.twig` are **not** deleted — the admin panel still uses them.

---

## Task 1: Convert `base_site.html.twig` — Navbar

**Files:**
- Modify: `templates/site/base_site.html.twig`

The goal of this task is to replace the Bootstrap 3 navbar (`data-toggle="collapse"`, `.dropdown`, glyphicons) with a Tailwind flex navbar. Dropdowns use CSS-only `group`/`group-hover:` utilities. The mobile hamburger uses a `<details>/<summary>` element. Bootstrap CSS/JS are **still loaded** throughout this task — they will be stripped in Task 6.

- [ ] **Step 1: Verify current tests pass**

```bash
docker compose exec php php bin/phpunit -c app/
```

Expected: all tests pass.

- [ ] **Step 2: Replace the `{% block nav %}` in `templates/site/base_site.html.twig`**

Replace the entire `{% block nav %}...{% endblock nav %}` section (lines 2–69 in the current file) with:

```twig
{% block nav %}
<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-14">

        {# Brand / homepage link #}
        <a href="{{ path('homepage') }}" class="font-semibold text-gray-800 hover:text-primary whitespace-nowrap mr-4">
            {{ 'site.menu.homepage'|trans }}
        </a>

        {# Desktop: main nav items (hidden on mobile) #}
        <div class="hidden md:flex items-center flex-1 gap-2 text-sm">
            {{ render(controller('AppBundle\\Controller\\SiteController::showMenuItemAction')) }}
        </div>

        {# Desktop: right-side controls (hidden on mobile) #}
        <div class="hidden md:flex items-center gap-4 text-sm ml-auto">

            {# Logged-in username #}
            {% if is_granted('IS_AUTHENTICATED_FULLY') %}
                <span class="text-gray-600">{{ app.user.username }}</span>
            {% endif %}

            {# Locale dropdown #}
            <div class="relative group">
                <button type="button" class="flex items-center gap-1 text-gray-600 hover:text-gray-900 cursor-pointer py-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                    </svg>
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <ul class="hidden group-hover:block absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded shadow-lg min-w-max z-50 py-1">
                    {% for locale in locales() %}
                        <li>
                            <a href="{{ path(app.request.get('_route', 'homepage'), app.request.get('_route_params', [])|merge({ _locale: locale.code })) }}"
                               class="block px-4 py-2 text-sm hover:bg-gray-100 {% if app.request.locale == locale.code %}font-semibold text-primary{% else %}text-gray-700{% endif %}">
                                {{ locale.name|capitalize }}
                            </a>
                        </li>
                    {% endfor %}
                </ul>
            </div>

            {# Search dropdown #}
            <div class="relative group">
                <button type="button" class="flex items-center gap-1 text-gray-600 hover:text-gray-900 cursor-pointer py-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
                <div class="hidden group-hover:block absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded shadow-lg z-50 p-3 min-w-max">
                    <div id="react-live-search"
                         data-placeholder="{{ 'common.search.placeholder'|trans }}"
                         data-locale="{{ app.request.locale }}"></div>
                </div>
            </div>

            {# User account dropdown #}
            <div class="relative group">
                <button type="button" class="flex items-center gap-1 text-gray-600 hover:text-gray-900 cursor-pointer py-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <ul class="hidden group-hover:block absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded shadow-lg min-w-max z-50 py-1">
                    {% if is_granted('IS_AUTHENTICATED_FULLY') %}
                        <li><a href="{{ path('logout') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ 'security.logout'|trans }}</a></li>
                    {% else %}
                        <li><a href="{{ path('security_login_form') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ 'security.login'|trans }}</a></li>
                        <li class="border-t border-gray-100"></li>
                        <li><a href="{{ path('user_registration') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ 'security.registration'|trans }}</a></li>
                        <li class="border-t border-gray-100"></li>
                    {% endif %}
                    {% if is_granted('ROLE_MANAGER') %}
                        <li><a href="{{ path('admin_index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ 'security.admin'|trans }}</a></li>
                    {% endif %}
                </ul>
            </div>
        </div>

        {# Mobile hamburger (visible only on mobile) #}
        <details class="md:hidden ml-auto group">
            <summary class="list-none cursor-pointer p-2 text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </summary>
            <div class="absolute left-0 right-0 top-14 bg-white border-b border-gray-200 shadow-lg z-50 px-4 py-3 flex flex-col gap-2 text-sm">
                <a href="{{ path('homepage') }}" class="py-1 text-gray-700 hover:text-primary">{{ 'site.menu.homepage'|trans }}</a>
                {{ render(controller('AppBundle\\Controller\\SiteController::showMenuItemAction')) }}
                <hr class="border-gray-200 my-1">
                {% for locale in locales() %}
                    <a href="{{ path(app.request.get('_route', 'homepage'), app.request.get('_route_params', [])|merge({ _locale: locale.code })) }}"
                       class="py-1 {% if app.request.locale == locale.code %}font-semibold text-primary{% else %}text-gray-700{% endif %} hover:text-primary">
                        {{ locale.name|capitalize }}
                    </a>
                {% endfor %}
                <hr class="border-gray-200 my-1">
                {% if is_granted('IS_AUTHENTICATED_FULLY') %}
                    <a href="{{ path('logout') }}" class="py-1 text-gray-700 hover:text-primary">{{ 'security.logout'|trans }}</a>
                {% else %}
                    <a href="{{ path('security_login_form') }}" class="py-1 text-gray-700 hover:text-primary">{{ 'security.login'|trans }}</a>
                    <a href="{{ path('user_registration') }}" class="py-1 text-gray-700 hover:text-primary">{{ 'security.registration'|trans }}</a>
                {% endif %}
                {% if is_granted('ROLE_MANAGER') %}
                    <a href="{{ path('admin_index') }}" class="py-1 text-gray-700 hover:text-primary">{{ 'security.admin'|trans }}</a>
                {% endif %}
            </div>
        </details>

    </div>
</nav>
{% endblock nav %}
```

- [ ] **Step 3: Run tests**

```bash
docker compose exec php php bin/phpunit -c app/
```

Expected: all tests pass.

- [ ] **Step 4: Smoke-check the page visually**

Open `http://localhost:8080/uk/` and verify:
- Navbar renders at the top as a white bar
- Desktop: nav links visible, globe/search/user icons on the right show hover dropdowns
- Mobile (shrink window to <768px): hamburger icon visible, clicking opens a stacked menu
- No JS console errors

- [ ] **Step 5: Commit**

```bash
git add templates/site/base_site.html.twig
git commit -m "Converted public site navbar from Bootstrap 3 to Tailwind CSS"
```

---

## Task 2: Convert Header, Body Grid, and Footer in `base_site.html.twig`

**Files:**
- Modify: `templates/site/base_site.html.twig`

Replace the Bootstrap 3 header (`.masthead`, `.well`, `col col-sm-6`), body grid (`col col-sm-3`/`col col-sm-9`), and footer (`.row`, `col-lg-12`, `.list-inline`) with Tailwind equivalents.

- [ ] **Step 1: Replace `{% block header %}` in `templates/site/base_site.html.twig`**

Replace the entire `{% block header %}...{% endblock header %}` section with:

```twig
{% block header %}
<header class="bg-gray-50 border-b border-gray-200 py-4">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <a href="{{ path('homepage') }}">
                    <img src="{{ asset("images/logo/logo_main.png") |imagine_filter('logo') }}"
                         alt="АН Радуга" class="h-16 w-auto">
                </a>
            </div>
            <div class="bg-white border border-gray-200 rounded p-3 shadow-sm text-sm">
                <p class="text-gray-600 mb-2 font-medium">{{ 'site.search'|trans }}</p>
                {{ render(controller("AppBundle\\Controller\\SiteController::searchAction")) }}
            </div>
        </div>
        <div class="mt-2 text-sm text-gray-500">
            {{ wo_render_breadcrumbs() }}
        </div>
    </div>
</header>
{% endblock header %}
```

- [ ] **Step 2: Replace `{% block body %}` in `templates/site/base_site.html.twig`**

Replace the entire `{% block body %}...{% endblock %}` section with:

```twig
{% block body %}
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex gap-6">
        <aside class="w-64 flex-shrink-0">
            {% block sidebar %}
                <div id="sidebar">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 flex items-center justify-between mb-2">
                        {{ 'site.offers'|trans }}
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </h3>
                    {{ render(controller('AppBundle\\Controller\\SiteController::menuAction')) }}
                </div>
            {% endblock sidebar %}
        </aside>
        <main class="flex-1 min-w-0">
            {% block panel %}{% endblock %}
        </main>
    </div>
</div>
{% endblock %}
```

- [ ] **Step 3: Replace `{% block footer %}` in `templates/site/base_site.html.twig`**

Replace the entire `{% block footer %}...{% endblock footer %}` section with:

```twig
{% block footer %}
<footer class="border-t border-gray-200 bg-gray-50 mt-8 py-4">
    <div class="max-w-7xl mx-auto px-4">
        <ul class="flex flex-wrap gap-4 text-sm text-gray-600 mb-2">
            <li><a href="{{ path('homepage') }}" class="hover:text-gray-900">{{ 'site.menu.homepage'|trans }}</a></li>
            {{ render(controller('AppBundle\\Controller\\SiteController::showMenuItemAction')) }}
        </ul>
        <p class="text-xs text-gray-400">Copyright &copy; Syrotchuk's Website 2016</p>
    </div>
</footer>
{% endblock footer %}
```

- [ ] **Step 4: Run tests**

```bash
docker compose exec php php bin/phpunit -c app/
```

Expected: all tests pass.

- [ ] **Step 5: Smoke-check layout**

Open `http://localhost:8080/uk/` and verify:
- Header shows logo left, search panel right
- Breadcrumbs render below header
- Body shows sidebar on the left (~256px), main content on the right
- Footer has nav links and copyright

- [ ] **Step 6: Commit**

```bash
git add templates/site/base_site.html.twig
git commit -m "Converted public site header, body grid and footer to Tailwind CSS"
```

---

## Task 3: Convert Category Sidebar (`menu.html.twig` + `menu-links.html.twig`)

**Files:**
- Modify: `templates/includes/menu.html.twig`
- Modify: `templates/includes/menu-links.html.twig`

Replace the Bootstrap 3 `.navbar-default.sidebar` / MetisMenu `#side-menu` structure with plain Tailwind nav using HTML `<details>/<summary>` for collapsible parent categories.

- [ ] **Step 1: Replace `templates/includes/menu.html.twig`**

```twig
<nav class="text-sm">
    <ul class="space-y-1">
        {% include "includes/menu-links.html.twig" with {'links': links} only %}
    </ul>
</nav>
```

- [ ] **Step 2: Replace `templates/includes/menu-links.html.twig`**

```twig
{% for link in links %}
    {% set title = link.title %}
    {% if not link.__children %}
        <li>
            <a href="{{ path('show_category', {'slug': link.title}) }}"
               class="block px-2 py-1 rounded text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                {{ title|trans }}
            </a>
        </li>
    {% else %}
        <li>
            <details class="group">
                <summary class="flex items-center justify-between px-2 py-1 rounded cursor-pointer text-gray-700 hover:bg-gray-100 hover:text-gray-900 list-none">
                    {{ title|trans }}
                    <svg class="w-3 h-3 flex-shrink-0 transition-transform group-open:rotate-180" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </summary>
                <ul class="ml-3 mt-1 space-y-1 border-l border-gray-200 pl-2">
                    {% include "includes/menu-links.html.twig" with {'links': link.__children} %}
                </ul>
            </details>
        </li>
    {% endif %}
{% endfor %}
```

- [ ] **Step 3: Run tests**

```bash
docker compose exec php php bin/phpunit -c app/
```

Expected: all tests pass.

- [ ] **Step 4: Smoke-check sidebar**

Open `http://localhost:8080/uk/` and verify:
- Sidebar shows category list
- Parent categories have a chevron indicator
- Clicking a parent opens/closes child list (HTML `<details>` native behaviour)
- Leaf items link to the correct `show_category` route

- [ ] **Step 5: Commit**

```bash
git add templates/includes/menu.html.twig templates/includes/menu-links.html.twig
git commit -m "Replaced MetisMenu sidebar with Tailwind details/summary category nav"
```

---

## Task 4: Convert Search Form to Tailwind Form Theme

**Files:**
- Create: `templates/form/tailwind_layout.html.twig`
- Modify: `templates/site/search.html.twig`

The header search form uses `bootstrap_3_horizontal_layout.html.twig` which outputs Bootstrap 3 markup. Create a minimal Tailwind form theme covering only the field types used (`choice` for selects, `checkbox` for except_floor, `text`/`number` for price).

- [ ] **Step 1: Create `templates/form/tailwind_layout.html.twig`**

```twig
{% use 'form_div_layout.html.twig' %}

{% block form_row %}
    <div class="mb-2">
        {{ form_label(form) }}
        {{ form_widget(form) }}
        {{ form_errors(form) }}
    </div>
{% endblock %}

{% block form_label %}
    {% if label is not same as(false) %}
        <label{% if id is defined %} for="{{ id }}"{% endif %} class="block text-xs font-medium text-gray-600 mb-1">
            {{ label|trans({}, translation_domain) }}
        </label>
    {% endif %}
{% endblock %}

{% block form_widget_simple %}
    <input type="{{ type }}" {{ block('widget_attributes') }} {% if value is defined %}value="{{ value }}"{% endif %}
           class="block w-full rounded border border-gray-300 px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"/>
{% endblock %}

{% block choice_widget_collapsed %}
    {% if multiple %}
        {% set attr = attr|merge({multiple: 'multiple'}) %}
    {% endif %}
    <select {{ block('widget_attributes') }}{% if multiple %} multiple{% endif %}
            class="block w-full rounded border border-gray-300 px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary bg-white">
        {% if placeholder is not none %}
            <option value=""{% if required and value is empty %} selected{% endif %}>{{ placeholder != '' ? (translation_domain is same as(false) ? placeholder : placeholder|trans({}, translation_domain)) : '' }}</option>
        {% endif %}
        {% if preferred_choices|length > 0 %}
            {% set options = preferred_choices %}
            {{- block('choice_widget_options') -}}
            {% if choices|length > 0 and separator is not none %}
                <option disabled="disabled">{{ separator }}</option>
            {% endif %}
        {% endif %}
        {% set options = choices %}
        {{- block('choice_widget_options') -}}
    </select>
{% endblock %}

{% block checkbox_widget %}
    <input type="checkbox" {{ block('widget_attributes') }}{% if value is defined %} value="{{ value }}"{% endif %}{% if checked %} checked{% endif %}
           class="rounded border-gray-300 text-primary focus:ring-primary"/>
{% endblock %}

{% block form_errors %}
    {% if errors|length > 0 %}
        <ul class="mt-1 text-xs text-red-600">
            {% for error in errors %}
                <li>{{ error.message }}</li>
            {% endfor %}
        </ul>
    {% endif %}
{% endblock %}
```

- [ ] **Step 2: Update `templates/site/search.html.twig`**

```twig
{% form_theme form 'form/tailwind_layout.html.twig' %}
{{ form_start(form, { attr: { class: 'flex flex-wrap gap-2 items-end' } }) }}
    <div>{{ form_row(form.category) }}</div>
    <div>{{ form_row(form.district) }}</div>
    <div>{{ form_row(form.price) }}</div>
    <div class="flex items-center gap-1 pt-4">
        {{ form_widget(form.except_floor) }}
        {{ form_label(form.except_floor) }}
    </div>
    <button type="submit" class="px-3 py-1 rounded bg-primary text-primary-foreground text-sm hover:opacity-90 mt-4">
        {{ 'common.search'|trans }}
    </button>
{{ form_end(form) }}
```

> **Note:** If the `common.search` translation key does not exist, add it in `translations/messages.uk.yml` and `translations/messages.en.yml`: `common.search: Пошук` / `common.search: Search`. Check with `grep -r 'common.search' translations/`.

- [ ] **Step 3: Run tests**

```bash
docker compose exec php php bin/phpunit -c app/
```

Expected: all tests pass.

- [ ] **Step 4: Smoke-check search form**

Open `http://localhost:8080/uk/` and verify:
- Header search panel shows category, district, price selects, except_floor checkbox, and submit button
- Submit navigates to `/uk/search_result` (or similar) without error
- Form fields are styled consistently (no Bootstrap form-group, form-control classes)

- [ ] **Step 5: Commit**

```bash
git add templates/form/tailwind_layout.html.twig templates/site/search.html.twig
git commit -m "Added Tailwind form theme and converted search form from Bootstrap 3"
```

---

## Task 5: Convert `show_estate.html.twig`

**Files:**
- Modify: `templates/site/show_estate.html.twig`

Replace Bootstrap 3 panels, `col-lg-9`/`col-lg-3`, `.media`/`.media-body`, `.well`, glyphicons, and `.btn.btn-info.btn-sm` with Tailwind equivalents. All React mount divs remain unchanged.

- [ ] **Step 1: Replace `{% block panel %}` in `templates/site/show_estate.html.twig`**

```twig
{% block panel %}
<div class="mb-5">
    {# Title + actions row #}
    <div class="flex items-start flex-wrap gap-3 mb-4">
        <h1 class="text-2xl font-bold text-gray-800 flex-1">{{ estate.title }}</h1>
        <div class="flex items-center gap-2">
            {% if is_granted('IS_AUTHENTICATED_FULLY') %}
                <div id="react-favorite-button"
                     data-slug="{{ estate.slug }}"
                     data-favorited="{{ app.user.hasEstate(estate) ? 'true' : 'false' }}"
                     data-csrf="{{ csrf_token('api_estate_favorite') }}"
                     data-label-add="{{ 'common.add_favorites'|trans }}"
                     data-label-remove="{{ 'common.delete_favorites'|trans }}">
                </div>
            {% endif %}
            <a href="{{ path('pdf_estate', { 'estate': estate.slug }) }}"
               class="inline-flex items-center gap-1 px-3 py-1.5 text-sm rounded border border-blue-500 text-blue-600 hover:bg-blue-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                {{ 'common.downloadPDF'|trans }}
            </a>
        </div>
    </div>

    {# Slideshow #}
    <div id="react-estate-slideshow"
         data-images="{{ estate.files|map(f => asset(f.path)|imagine_filter('large'))|json_encode }}">
    </div>
</div>

{# Two-column layout: 3/4 main + 1/4 sidebar #}
<div class="flex gap-6 flex-wrap">

    {# Main column #}
    <div class="flex-1 min-w-0 space-y-4">

        {# Description card #}
        <div class="rounded border border-gray-200 shadow-sm">
            <div class="px-4 py-2 border-b border-gray-200 bg-gray-50 flex items-center gap-2 text-sm font-semibold text-gray-700">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                {{ 'common.description'|trans }}
            </div>
            <div class="p-4 text-sm text-gray-700 leading-relaxed">
                {{ estate.description }}
            </div>
        </div>

        {# Comments card #}
        <div class="rounded border border-gray-200 shadow-sm">
            <div class="px-4 py-2 border-b border-gray-200 bg-gray-50 flex items-center gap-2 text-sm font-semibold text-gray-700">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                {{ 'common.comments'|trans }}
            </div>
            <div class="p-4">
                {% if is_granted('IS_AUTHENTICATED_FULLY') %}
                    <div class="bg-gray-50 border border-gray-200 rounded p-3 mb-4">
                        <div id="react-comment-form"
                             data-slug="{{ estate.slug }}"
                             data-csrf="{{ csrf_token('api_estate_comment') }}"
                             data-placeholder="{{ 'site.comment_placeholder'|trans }}"
                             data-submit="{{ 'site.comment_submit'|trans }}">
                        </div>
                    </div>
                {% else %}
                    <p class="text-sm text-gray-500 mb-3">{{ 'site.sing_in_please'|trans }}</p>
                    <hr class="border-gray-200 mb-3">
                {% endif %}

                <div class="space-y-3">
                    {% for comment in estate.comments %}
                        {% if comment.enabled %}
                            <div class="border-b border-gray-100 pb-3 last:border-0">
                                <div class="flex items-baseline gap-2 mb-1">
                                    <span class="text-sm font-medium text-gray-800">{{ comment.createdBy }}</span>
                                    <span class="text-xs text-gray-400">{{ comment.createdAt|date('Y-m-d H:i:s') }}</span>
                                </div>
                                <p class="text-sm text-gray-700">{{ comment.content }}</p>
                            </div>
                        {% endif %}
                    {% endfor %}
                </div>
            </div>
        </div>
    </div>

    {# Right sidebar column #}
    <div class="w-64 flex-shrink-0">
        <div id="react-estate-info-panel" data-slug="{{ estate.slug }}"></div>
        <div class="mt-4 flex gap-2 text-sm">
            <span>{{ googlePlusButton() }}</span>
            <span>{{ twitterButton() }}</span>
            <span>{{ facebookButton() }}</span>
        </div>
    </div>

</div>
{% endblock panel %}
```

- [ ] **Step 2: Run tests**

```bash
docker compose exec php php bin/phpunit -c app/
```

Expected: all tests pass.

- [ ] **Step 3: Smoke-check estate detail page**

Open `http://localhost:8080/uk/<any-estate-slug>` and verify:
- Title and PDF download button render at the top
- Slideshow React island mounts correctly
- Description card renders below slideshow
- Comments card shows comment form (if logged in) or login prompt
- Existing comments display with author + date
- Right sidebar shows estate info panel React island
- Social share buttons render

- [ ] **Step 4: Commit**

```bash
git add templates/site/show_estate.html.twig
git commit -m "Converted estate detail page from Bootstrap 3 panels to Tailwind cards"
```

---

## Task 6: Convert `index.html.twig`

**Files:**
- Modify: `templates/site/index.html.twig`

Replace `.panel`, `col col-sm-8`, `col col-sm-4`, `.img-responsive`, `.btn.btn-default` with Tailwind equivalents. The React estate listing island (`#react-estate-listing`) path is left unchanged.

- [ ] **Step 1: Replace `{% block panel %}` in `templates/site/index.html.twig`**

```twig
{% extends 'site/base_site.html.twig' %}
{% block panel %}
    {% if apiUrl is defined %}
        <div id="react-estate-listing" data-api-url="{{ apiUrl }}" data-locale="{{ app.request.locale }}"></div>
    {% else %}
        <div class="space-y-6">
            {% for estate in pagination %}
                <div class="rounded border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-4">
                        <h2 class="text-lg font-semibold mb-3">
                            <a class="text-gray-800 hover:text-primary"
                               href="{{ path('show_estate', { 'slug': estate.slug }) }}">
                                {{ estate.title }}
                            </a>
                        </h2>

                        <div class="flex gap-4">
                            <div class="flex-1 min-w-0">
                                {% if estate.mainFoto is not null %}
                                    <a href="{{ path('show_estate', { 'slug': estate.slug }) }}">
                                        <img alt="фото квартиры купить Черкассы"
                                             src="{{ asset(estate.mainFoto.path) |imagine_filter('large') }}"
                                             class="w-full h-auto rounded">
                                    </a>
                                {% else %}
                                    <a href="{{ path('show_estate', { 'slug': estate.slug }) }}">
                                        <img alt="фото квартиры купить Черкассы"
                                             src="{{ asset(estate.files[0].path) |imagine_filter('large') }}"
                                             class="w-full h-auto rounded">
                                    </a>
                                {% endif %}
                            </div>
                            <div class="w-40 flex-shrink-0 space-y-2">
                                {% if estate.files[1] is defined %}
                                    {% if estate.files[1] != estate.mainFoto %}
                                        <a href="{{ path('show_estate', { 'slug': estate.slug }) }}">
                                            <img alt="фото квартиры купить Черкассы"
                                                 src="{{ asset("#{estate.files[1].path}") |imagine_filter('medium') }}"
                                                 class="w-full h-auto rounded">
                                        </a>
                                    {% endif %}
                                {% endif %}
                                {% if estate.files[2] is defined %}
                                    <a href="{{ path('show_estate', { 'slug': estate.slug }) }}">
                                        <img alt="фото квартиры купить Черкассы"
                                             src="{{ asset("#{estate.files[2].path}") |imagine_filter('medium') }}"
                                             class="w-full h-auto rounded">
                                    </a>
                                {% endif %}
                            </div>
                        </div>

                        <h3 class="text-sm font-semibold text-gray-600 mt-3 mb-1">{{ 'site.summary'|trans }}:</h3>
                        <div class="text-sm text-gray-700">{{ estate.description }}</div>
                        <div class="mt-3">
                            <a href="{{ path('show_estate', { 'slug': estate.slug }) }}"
                               class="inline-block px-3 py-1.5 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-50">
                                {{ 'site.more'|trans }}
                            </a>
                        </div>
                    </div>
                </div>
            {% endfor %}

            <div class="mt-4">
                {{ knp_pagination_render(pagination) }}
            </div>
        </div>
    {% endif %}
{% endblock panel %}
```

- [ ] **Step 2: Run tests**

```bash
docker compose exec php php bin/phpunit -c app/
```

Expected: all tests pass.

- [ ] **Step 3: Smoke-check index page**

Open `http://localhost:8080/uk/` and verify:
- Estate listings render as bordered cards
- Each card shows title, two-column image layout, summary, and "more" link
- KNP pagination renders at the bottom
- If `apiUrl` is defined in the response, the React island mounts instead

- [ ] **Step 4: Commit**

```bash
git add templates/site/index.html.twig
git commit -m "Converted estate index listing from Bootstrap 3 to Tailwind CSS"
```

---

## Task 7: Drop Bootstrap CSS/JS from Public Site

**Files:**
- Modify: `templates/site/base_site.html.twig`

Now that all public site HTML uses Tailwind classes, override the `stylesheets` and `javascripts` blocks in `base_site.html.twig` to skip loading `css/app.css` (Bootstrap 3 CSS), `js/app.js` (jQuery + Bootstrap 3 JS bundle), and `js/sb-admin-2.js`. The admin panel extends `base_admin.html.twig` → `base.html.twig` and is **unaffected** by this change.

- [ ] **Step 1: Verify all public pages render correctly before removing Bootstrap**

```bash
docker compose exec php php bin/phpunit -c app/
```

Expected: all tests pass.

- [ ] **Step 2: Add `stylesheets` block override to `base_site.html.twig`**

Add the following block immediately before `{% block nav %}` (top of the file, after `{% extends 'base.html.twig' %}`):

```twig
{% block stylesheets %}
    {{ encore_entry_link_tags('app') }}
{% endblock stylesheets %}
```

This replaces the parent's block which loaded `css/app.css` (Bootstrap) before Tailwind. Now only the Tailwind bundle loads.

- [ ] **Step 3: Replace `{% block javascripts %}` in `base_site.html.twig`**

Replace the existing:

```twig
{% block javascripts %}
    {{ parent() }}
    <script src="{{ asset('bundles/fosjsrouting/js/router.js') }}"></script>
    <script src="{{ path('fos_js_routing_js', {'callback': 'fos.Router.setData'}) }}"></script>
{{ encore_entry_script_tags('site') }}
{% endblock javascripts %}
```

With (no `{{ parent() }}` — skips Bootstrap JS from `base.html.twig`):

```twig
{% block javascripts %}
    <script src="{{ asset('bundles/fosjsrouting/js/router.js') }}"></script>
    <script src="{{ path('fos_js_routing_js', {'callback': 'fos.Router.setData'}) }}"></script>
    {{ encore_entry_script_tags('site') }}
{% endblock javascripts %}
```

- [ ] **Step 4: Run tests**

```bash
docker compose exec php php bin/phpunit -c app/
```

Expected: all tests pass.

- [ ] **Step 5: Smoke-check public site without Bootstrap**

Open `http://localhost:8080/uk/` in the browser:
- Verify no Bootstrap CSS or JS in the network panel (no `app.css` from `public/css/`, no `app.js` from `public/js/`)
- Verify navbar, header, sidebar, footer all render correctly
- Verify dropdown hover menus work
- Verify mobile hamburger works
- Verify sidebar category `<details>` expand/collapse works
- Open an estate detail page — verify slideshow, favorite button, comments all work

Also verify the admin panel is **not broken**:
- Open `http://localhost:8080/en/admin/` and confirm it still loads Bootstrap CSS and JS (`app.css`, `app.js` in network panel)
- Admin sidebar dropdown should still work

- [ ] **Step 6: Commit**

```bash
git add templates/site/base_site.html.twig
git commit -m "Removed Bootstrap 3 CSS and JS from public site — Tailwind-only layout"
```

---

## Task 8: Phase 4 Cleanup — Remove Legacy Build Artefacts

**Files:**
- Delete: `bower.json`
- Delete: `gulpfile.js`
- Delete: `bower_components/` directory
- Delete: `public/js/livesearch.js`
- Delete: `public/js/pgwslideshow.js`
- Delete: `public/js/scripts.js`

> **Not deleted:** `public/css/app.css`, `public/js/app.js`, `public/js/sb-admin-2.js` — the admin panel still loads these via `base.html.twig`.

- [ ] **Step 1: Confirm none of the files to delete are referenced anywhere**

```bash
grep -r "livesearch.js\|pgwslideshow.js\|scripts.js" templates/ public/ assets/ --include="*.twig" --include="*.ts" --include="*.tsx" --include="*.html"
grep -r "bower\|gulpfile" templates/ src/ assets/ --include="*.twig" --include="*.php" --include="*.ts" --include="*.tsx"
```

Expected: no matches in any template or source file. If any match is found, investigate before deleting.

- [ ] **Step 2: Delete unused legacy files**

```bash
rm bower.json gulpfile.js
rm public/js/livesearch.js public/js/pgwslideshow.js public/js/scripts.js
rm -rf bower_components/
```

- [ ] **Step 3: Run tests to confirm nothing broke**

```bash
docker compose exec php php bin/phpunit -c app/
```

Expected: all tests pass.

- [ ] **Step 4: Smoke-check one more time**

Open `http://localhost:8080/uk/` — site loads correctly.
Open `http://localhost:8080/en/admin/` — admin loads correctly.

- [ ] **Step 5: Commit**

```bash
git add -u
git add bower.json gulpfile.js public/js/livesearch.js public/js/pgwslideshow.js public/js/scripts.js
git commit -m "Removed bower, gulp, bower_components and unused legacy public JS files (Phase 4 cleanup)"
```

> **Note:** `git add -u` stages deletions; then explicitly name the deleted files to be safe.

---

## Self-Review

### Spec coverage

| Requirement | Task |
|---|---|
| Replace Bootstrap 3 navbar (dropdowns, locale, search, user) | Task 1 |
| Replace Bootstrap 3 header (masthead, well, col-sm-6) | Task 2 |
| Replace Bootstrap 3 body grid (col-sm-3/9) | Task 2 |
| Replace Bootstrap 3 footer (list-inline, col-lg-12) | Task 2 |
| Replace MetisMenu sidebar | Task 3 |
| Replace Bootstrap 3 form theme on search form | Task 4 |
| Replace Bootstrap 3 panel/media/glyphicons in show_estate | Task 5 |
| Replace Bootstrap 3 panel/col/button in index | Task 6 |
| Remove Bootstrap CSS/JS from public site | Task 7 |
| Remove bower/gulp/bower_components | Task 8 |
| Remove unused legacy JS files | Task 8 |
| Admin panel continues to work throughout | Addressed in Task 7 notes |
| All 97 tests pass after each task | Verified in every task |

### Placeholder scan

No TBDs, TODOs, or "implement later" markers present. All code blocks are complete.

### Type consistency

- `estate.files|map(f => asset(f.path)|imagine_filter('large'))` — same Twig map syntax as original
- `estate.mainFoto`, `estate.slug`, `estate.title`, `estate.description`, `estate.comments` — same property names as original
- `comment.createdBy`, `comment.createdAt`, `comment.content`, `comment.enabled` — same as original
- `link.__children`, `link.title` — same as original menu-links template
- All route names (`homepage`, `show_estate`, `show_category`, `logout`, `security_login_form`, `user_registration`, `admin_index`, `pdf_estate`) — same as original
