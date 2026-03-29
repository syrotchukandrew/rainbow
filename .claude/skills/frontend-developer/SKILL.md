---
name: frontend-developer
description: Use when working on Twig templates, CSS, JavaScript, or frontend assets in the Rainbow project
---

# Frontend Developer

## Overview

Frontend guide for the Rainbow project. UI is server-rendered via Twig with traditional asset management (no Node-based SPA framework).

## Stack

- **Templates:** Twig (in `templates/` or `app/Resources/views/`)
- **CSS:** Custom styles + Bootstrap (check `web/` or `assets/`)
- **JS:** Vanilla JS / jQuery (check existing patterns before adding libraries)
- **Assets:** Symfony Asset component; built assets in `web/`
- **Images/media:** `web/media/`, `web/images/` (gitignored for generated content)

## Twig Conventions

- Use `{{ asset('...') }}` for static files, `{{ path('route_name') }}` for URLs
- Never use `|raw` without explicit XSS review — Twig auto-escapes by default
- Extend base layout: `{% extends 'base.html.twig' %}` (verify actual base template path)
- Translations: `{{ 'key'|trans }}` — default locale is `uk` (Ukrainian)
- Block structure: `{% block content %}...{% endblock %}`

## Asset Management

```bash
# Clear cache after template changes if cached
php bin/console cache:clear
```

Check `webpack.config.js` or `gulpfile.js` if a build step exists before editing compiled assets.

## Translations

Default locale: **Ukrainian (`uk`)**. Translation files are in `translations/`.
- Add new keys to `messages.uk.yml` (or `.xlf`) and mirror in other locale files
- Never hardcode user-visible strings in templates — use translation keys

## Responsive / Accessibility

- Mobile-first where possible
- Use semantic HTML (`<nav>`, `<main>`, `<article>`, etc.)
- Form labels must be associated with inputs (`for`/`id` or wrapping `<label>`)

## Quick Reference

| Task | How |
|------|-----|
| Link to route | `{{ path('app_route_name') }}` |
| Static asset | `{{ asset('images/logo.png') }}` |
| Translate | `{{ 'nav.home'|trans }}` |
| Include partial | `{% include 'partials/_card.html.twig' %}` |
| Form rendering | `{{ form_start(form) }}` / `{{ form_widget(form) }}` |
| Current user | `{{ app.user }}` |
| Flash messages | `app.flashes('success')` |