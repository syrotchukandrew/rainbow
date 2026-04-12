# React Frontend Migration — Phase 0: Infrastructure

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the Gulp + LESS + Bower build pipeline with Symfony Webpack Encore, add React 18 + TypeScript, and configure Tailwind CSS + shadcn/ui — proving the island pattern works end-to-end with a smoke-test component.

**Architecture:** React components are mounted as "islands" inside existing Twig page shells. Symfony continues to own routing and HTML rendering. Each React island mounts into a dedicated `<div id="react-*">` node. Existing `web/css/app.css` and `web/js/app.js` are kept intact during this phase — they will be removed in Phase 4.

**Tech Stack:** Symfony Webpack Encore 4.x, React 18, TypeScript 5, Tailwind CSS 3.4, shadcn/ui, PostCSS

---

## File Map

### New files
| Path | Purpose |
|------|---------|
| `webpack.config.js` | Encore build config — entry points, aliases, loaders |
| `tsconfig.json` | TypeScript config — IDE support + type checking |
| `postcss.config.js` | PostCSS config — enables Tailwind |
| `tailwind.config.js` | Tailwind content paths + shadcn/ui theme extension |
| `components.json` | shadcn/ui registry config |
| `assets/app.ts` | Shared entry — imports globals.css (Tailwind base) |
| `assets/styles/globals.css` | Tailwind directives + shadcn/ui CSS variables |
| `assets/lib/utils.ts` | `cn()` utility (clsx + tailwind-merge) |
| `assets/components/ui/button.tsx` | shadcn/ui Button component (smoke test) |
| `assets/site/entry.tsx` | Public site React bundle entry point |
| `assets/admin/entry.tsx` | Admin panel React bundle entry point |
| `assets/site/components/HelloIsland.tsx` | Smoke-test React island |
| `config/packages/webpack_encore.yaml` | Encore bundle Symfony config |

### Modified files
| Path | Change |
|------|--------|
| `package.json` | Add Encore + React + TypeScript + Tailwind devDependencies |
| `composer.json` | Add `symfony/webpack-encore-bundle` |
| `templates/base.html.twig` | Add Encore CSS/JS helpers alongside existing assets |
| `templates/site/base_site.html.twig` | Add `encore_entry_script_tags('site')` |
| `templates/admin/base_admin.html.twig` | Add `encore_entry_script_tags('admin')` |
| `templates/site/index.html.twig` | Add smoke-test island mount point |
| `.gitignore` | Add `.superpowers/` |

---

## Task 1: Install Encore PHP bundle

**Files:**
- Modify: `composer.json` (via composer)

- [ ] **Step 1: Require the Encore bundle**

```bash
docker compose exec php composer require symfony/webpack-encore-bundle
```

Expected output ends with: `symfony/webpack-encore-bundle` installed, no errors.

- [ ] **Step 2: Verify the bundle is registered**

```bash
docker compose exec php grep -r "WebpackEncoreBundle" config/bundles.php
```

Expected: `Symfony\WebpackEncoreBundle\WebpackEncoreBundle::class => ['all' => true]`

---

## Task 2: Install npm packages

**Files:**
- Modify: `package.json`

- [ ] **Step 1: Install Encore and React runtime packages**

```bash
npm install --save-dev @symfony/webpack-encore webpack webpack-cli babel-loader @babel/core @babel/preset-env @babel/preset-react core-js ts-loader typescript
npm install react react-dom
npm install --save-dev @types/react @types/react-dom
```

- [ ] **Step 2: Install Tailwind and PostCSS**

```bash
npm install --save-dev tailwindcss@3 postcss autoprefixer postcss-loader css-loader style-loader
```

- [ ] **Step 3: Install shadcn/ui runtime dependencies**

```bash
npm install class-variance-authority clsx tailwind-merge lucide-react @radix-ui/react-slot
```

- [ ] **Step 4: Verify installs succeeded**

```bash
node -e "require('@symfony/webpack-encore'); require('react'); console.log('OK')"
```

Expected: `OK`

---

## Task 3: Create webpack.config.js

**Files:**
- Create: `webpack.config.js`

- [ ] **Step 1: Create the Encore config**

```js
// webpack.config.js
const Encore = require('@symfony/webpack-encore');
const path = require('path');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('web/build/')
    .setPublicPath('/build')

    // Entry points — one bundle per section
    .addEntry('app', './assets/app.ts')
    .addEntry('site', './assets/site/entry.tsx')
    .addEntry('admin', './assets/admin/entry.tsx')

    // Split shared vendor code into a runtime chunk
    .splitEntryChunks()
    .enableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // Babel: core-js polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })

    // ts-loader handles .ts/.tsx (type stripping + type checking)
    // enableReactPreset adds @babel/preset-react for JSX → JS transform
    .enableTypeScriptLoader()
    .enableReactPreset()

    // PostCSS (Tailwind runs through here)
    .enablePostCssLoader()

    // Path alias: @/ → assets/
    .addAliases({
        '@': path.resolve(__dirname, 'assets'),
    })
;

module.exports = Encore.getWebpackConfig();
```

- [ ] **Step 2: Add build scripts to package.json**

Open `package.json` and replace the `"scripts"` block:

```json
"scripts": {
    "dev": "encore dev",
    "dev-server": "encore dev-server",
    "watch": "encore dev --watch",
    "build": "encore production"
}
```

---

## Task 4: Create tsconfig.json

**Files:**
- Create: `tsconfig.json`

- [ ] **Step 1: Create TypeScript config**

This is for IDE support and standalone type checking (`tsc --noEmit`). The actual build uses Babel (Task 3) which strips types without checking them. Run `tsc --noEmit` separately in CI to catch type errors.

```json
{
  "compilerOptions": {
    "target": "ES2020",
    "module": "ESNext",
    "moduleResolution": "bundler",
    "lib": ["ES2020", "DOM", "DOM.Iterable"],
    "jsx": "react-jsx",
    "strict": true,
    "esModuleInterop": true,
    "skipLibCheck": true,
    "forceConsistentCasingInFileNames": true,
    "allowJs": false,
    "noEmit": true,
    "baseUrl": ".",
    "paths": {
      "@/*": ["assets/*"]
    }
  },
  "include": ["assets/**/*"],
  "exclude": ["node_modules", "web"]
}
```

- [ ] **Step 2: Verify TypeScript recognises the config**

```bash
npx tsc --version
```

Expected: `Version 5.x.x`

---

## Task 5: Configure Tailwind CSS + PostCSS

**Files:**
- Create: `postcss.config.js`
- Create: `tailwind.config.js`

- [ ] **Step 1: Create PostCSS config**

```js
// postcss.config.js
module.exports = {
    plugins: {
        tailwindcss: {},
        autoprefixer: {},
    },
};
```

- [ ] **Step 2: Create Tailwind config**

The `content` array tells Tailwind which files to scan for class names. Includes all Twig templates and all React components.

```js
// tailwind.config.js
/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './templates/**/*.html.twig',
        './assets/**/*.{ts,tsx}',
    ],
    theme: {
        extend: {
            colors: {
                border: 'hsl(var(--border))',
                input: 'hsl(var(--input))',
                ring: 'hsl(var(--ring))',
                background: 'hsl(var(--background))',
                foreground: 'hsl(var(--foreground))',
                primary: {
                    DEFAULT: 'hsl(var(--primary))',
                    foreground: 'hsl(var(--primary-foreground))',
                },
                secondary: {
                    DEFAULT: 'hsl(var(--secondary))',
                    foreground: 'hsl(var(--secondary-foreground))',
                },
                destructive: {
                    DEFAULT: 'hsl(var(--destructive))',
                    foreground: 'hsl(var(--destructive-foreground))',
                },
                muted: {
                    DEFAULT: 'hsl(var(--muted))',
                    foreground: 'hsl(var(--muted-foreground))',
                },
                accent: {
                    DEFAULT: 'hsl(var(--accent))',
                    foreground: 'hsl(var(--accent-foreground))',
                },
                popover: {
                    DEFAULT: 'hsl(var(--popover))',
                    foreground: 'hsl(var(--popover-foreground))',
                },
                card: {
                    DEFAULT: 'hsl(var(--card))',
                    foreground: 'hsl(var(--card-foreground))',
                },
            },
            borderRadius: {
                lg: 'var(--radius)',
                md: 'calc(var(--radius) - 2px)',
                sm: 'calc(var(--radius) - 4px)',
            },
        },
    },
    plugins: [],
};
```

---

## Task 6: Create assets/ directory structure

**Files:**
- Create: `assets/styles/globals.css`
- Create: `assets/lib/utils.ts`
- Create: `assets/app.ts`
- Create: `assets/site/entry.tsx`
- Create: `assets/admin/entry.tsx`

- [ ] **Step 1: Create Tailwind globals with shadcn/ui CSS variables**

```css
/* assets/styles/globals.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  :root {
    --background: 0 0% 100%;
    --foreground: 222.2 84% 4.9%;
    --card: 0 0% 100%;
    --card-foreground: 222.2 84% 4.9%;
    --popover: 0 0% 100%;
    --popover-foreground: 222.2 84% 4.9%;
    --primary: 222.2 47.4% 11.2%;
    --primary-foreground: 210 40% 98%;
    --secondary: 210 40% 96.1%;
    --secondary-foreground: 222.2 47.4% 11.2%;
    --muted: 210 40% 96.1%;
    --muted-foreground: 215.4 16.3% 46.9%;
    --accent: 210 40% 96.1%;
    --accent-foreground: 222.2 47.4% 11.2%;
    --destructive: 0 84.2% 60.2%;
    --destructive-foreground: 210 40% 98%;
    --border: 214.3 31.8% 91.4%;
    --input: 214.3 31.8% 91.4%;
    --ring: 222.2 84% 4.9%;
    --radius: 0.5rem;
  }
  .dark {
    --background: 222.2 84% 4.9%;
    --foreground: 210 40% 98%;
    --card: 222.2 84% 4.9%;
    --card-foreground: 210 40% 98%;
    --popover: 222.2 84% 4.9%;
    --popover-foreground: 210 40% 98%;
    --primary: 210 40% 98%;
    --primary-foreground: 222.2 47.4% 11.2%;
    --secondary: 217.2 32.6% 17.5%;
    --secondary-foreground: 210 40% 98%;
    --muted: 217.2 32.6% 17.5%;
    --muted-foreground: 215 20.2% 65.1%;
    --accent: 217.2 32.6% 17.5%;
    --accent-foreground: 210 40% 98%;
    --destructive: 0 62.8% 30.6%;
    --destructive-foreground: 210 40% 98%;
    --border: 217.2 32.6% 17.5%;
    --input: 217.2 32.6% 17.5%;
    --ring: 212.7 26.8% 83.9%;
  }
}
```

- [ ] **Step 2: Create the `cn()` utility**

```ts
// assets/lib/utils.ts
import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]): string {
    return twMerge(clsx(inputs));
}
```

- [ ] **Step 3: Create the shared app entry point**

```ts
// assets/app.ts
import './styles/globals.css';
```

- [ ] **Step 4: Create the site entry point (stub)**

```tsx
// assets/site/entry.tsx
import React from 'react';
import ReactDOM from 'react-dom/client';

// Island mount helper — mounts a React component into a DOM node if it exists
export function mountIsland(
    id: string,
    Component: React.ComponentType,
): void {
    const el = document.getElementById(id);
    if (el) {
        ReactDOM.createRoot(el).render(
            <React.StrictMode>
                <Component />
            </React.StrictMode>,
        );
    }
}

// Islands registered below — each component checks for its mount node
// Phase 1 will populate this section
```

- [ ] **Step 5: Create the admin entry point (stub)**

```tsx
// assets/admin/entry.tsx
import React from 'react';
import ReactDOM from 'react-dom/client';

export function mountIsland(
    id: string,
    Component: React.ComponentType,
): void {
    const el = document.getElementById(id);
    if (el) {
        ReactDOM.createRoot(el).render(
            <React.StrictMode>
                <Component />
            </React.StrictMode>,
        );
    }
}

// Admin islands registered below — Phase 2 will populate this section
```

---

## Task 7: Set up shadcn/ui

**Files:**
- Create: `components.json`
- Create: `assets/components/ui/button.tsx`

- [ ] **Step 1: Create components.json**

This tells the `shadcn` CLI where to put components and how paths are resolved.

```json
{
  "$schema": "https://ui.shadcn.com/schema.json",
  "style": "default",
  "rsc": false,
  "tsx": true,
  "tailwind": {
    "config": "tailwind.config.js",
    "css": "assets/styles/globals.css",
    "baseColor": "slate",
    "cssVariables": true,
    "prefix": ""
  },
  "aliases": {
    "components": "@/components",
    "utils": "@/lib/utils",
    "ui": "@/components/ui",
    "lib": "@/lib",
    "hooks": "@/hooks"
  }
}
```

- [ ] **Step 2: Add the Button component**

Run the shadcn CLI to scaffold Button (it reads `components.json` for paths):

```bash
npx shadcn@latest add button --yes
```

This creates `assets/components/ui/button.tsx`. If the CLI fails due to framework detection issues, create it manually:

```tsx
// assets/components/ui/button.tsx
import * as React from 'react';
import { Slot } from '@radix-ui/react-slot';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const buttonVariants = cva(
    'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
    {
        variants: {
            variant: {
                default: 'bg-primary text-primary-foreground hover:bg-primary/90',
                destructive: 'bg-destructive text-destructive-foreground hover:bg-destructive/90',
                outline: 'border border-input bg-background hover:bg-accent hover:text-accent-foreground',
                secondary: 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
                ghost: 'hover:bg-accent hover:text-accent-foreground',
                link: 'text-primary underline-offset-4 hover:underline',
            },
            size: {
                default: 'h-10 px-4 py-2',
                sm: 'h-9 rounded-md px-3',
                lg: 'h-11 rounded-md px-8',
                icon: 'h-10 w-10',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    },
);

export interface ButtonProps
    extends React.ButtonHTMLAttributes<HTMLButtonElement>,
        VariantProps<typeof buttonVariants> {
    asChild?: boolean;
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
    ({ className, variant, size, asChild = false, ...props }, ref) => {
        const Comp = asChild ? Slot : 'button';
        return (
            <Comp
                className={cn(buttonVariants({ variant, size, className }))}
                ref={ref}
                {...props}
            />
        );
    },
);
Button.displayName = 'Button';

export { Button, buttonVariants };
```

---

## Task 8: Create smoke-test island

**Files:**
- Create: `assets/site/components/HelloIsland.tsx`
- Modify: `assets/site/entry.tsx`
- Modify: `templates/site/index.html.twig`

- [ ] **Step 1: Create the HelloIsland component**

```tsx
// assets/site/components/HelloIsland.tsx
import React from 'react';
import { Button } from '@/components/ui/button';

export default function HelloIsland(): React.JSX.Element {
    const [clicked, setClicked] = React.useState(false);

    return (
        <div className="p-4 border border-green-500 rounded-md bg-green-50 inline-block">
            <p className="text-green-800 font-medium mb-2">
                ✓ React island loaded
            </p>
            <Button
                variant="outline"
                size="sm"
                onClick={() => setClicked(!clicked)}
            >
                {clicked ? 'It works!' : 'Click to test'}
            </Button>
        </div>
    );
}
```

- [ ] **Step 2: Register the island in site/entry.tsx**

Replace the full content of `assets/site/entry.tsx`:

```tsx
// assets/site/entry.tsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import HelloIsland from './components/HelloIsland';

function mountIsland(
    id: string,
    Component: React.ComponentType,
): void {
    const el = document.getElementById(id);
    if (el) {
        ReactDOM.createRoot(el).render(
            <React.StrictMode>
                <Component />
            </React.StrictMode>,
        );
    }
}

mountIsland('react-hello-island', HelloIsland);
```

- [ ] **Step 3: Add the mount point to the homepage template**

Open `templates/site/index.html.twig`. Find the opening of the main content block (look for `{% block panel %}` or the first `<div class="container">`). Add the mount point div just inside it, before any existing content:

```twig
<div id="react-hello-island" style="margin-bottom: 1rem;"></div>
```

---

## Task 9: Configure Symfony Encore bundle

**Files:**
- Create: `config/packages/webpack_encore.yaml`
- Modify: `templates/base.html.twig`
- Modify: `templates/site/base_site.html.twig`
- Modify: `templates/admin/base_admin.html.twig`

- [ ] **Step 1: Create webpack_encore.yaml**

```yaml
# config/packages/webpack_encore.yaml
webpack_encore:
    output_path: '%kernel.project_dir%/web/build'
    script_attributes:
        defer: true
    preload: false
    cache: false
    crossorigin: 'anonymous'
```

- [ ] **Step 2: Update base.html.twig to load Encore's app bundle**

In `templates/base.html.twig`, update the `{% block stylesheets %}` block to include Encore's output alongside the existing CSS (keep both for now):

```twig
{% block stylesheets %}
    <link rel="stylesheet" href="{{ absolute_url(asset('css/app.css')) }}" media="screen">
    {{ encore_entry_link_tags('app') }}
{% endblock stylesheets %}
```

- [ ] **Step 3: Add site bundle to the site base template**

In `templates/site/base_site.html.twig`, find `{% block javascripts %}` (or add it before `{% endblock %}`). Add the site bundle after the existing scripts:

```twig
{% block javascripts %}
    {{ parent() }}
    {{ encore_entry_script_tags('site') }}
{% endblock javascripts %}
```

- [ ] **Step 4: Add admin bundle to the admin base template**

In `templates/admin/base_admin.html.twig`, find `{% block javascripts %}` (or add it before `{% endblock %}`). Add the admin bundle:

```twig
{% block javascripts %}
    {{ parent() }}
    {{ encore_entry_script_tags('admin') }}
{% endblock javascripts %}
```

---

## Task 10: Update .gitignore

**Files:**
- Modify: `.gitignore`

- [ ] **Step 1: Add generated build output and superpowers session dir**

```bash
echo "" >> .gitignore
echo "# Webpack Encore build output" >> .gitignore
echo "/web/build/" >> .gitignore
echo "" >> .gitignore
echo "# Superpowers brainstorm sessions" >> .gitignore
echo "/.superpowers/" >> .gitignore
```

---

## Task 11: Verify the full setup

- [ ] **Step 1: Run the development build**

```bash
npm run dev
```

Expected: Build completes. Output shows entries for `app`, `site`, `admin`. Files appear in `web/build/`.

```bash
ls web/build/
```

Expected: `app.css`, `site.js`, `admin.js` (or versioned equivalents), `entrypoints.json`, `manifest.json`.

- [ ] **Step 2: Clear Symfony cache**

```bash
docker compose exec php php bin/console cache:clear
```

Expected: `Cache for the "dev" environment (debug=true) was successfully cleared.`

- [ ] **Step 3: Check the homepage loads without errors**

Open `http://localhost` (or your dev URL) in a browser. Verify:
- Page loads (HTTP 200)
- Existing styles still apply (Bootstrap 3 CSS still works — `web/css/app.css` still loaded)
- The green "React island loaded" box appears on the homepage
- Clicking the button toggles its text
- Browser console has no errors

- [ ] **Step 4: Run PHP tests**

```bash
docker compose exec php php bin/phpunit -c app/
```

Expected: All 49 tests pass. Zero failures, zero errors.

- [ ] **Step 5: Run TypeScript type check**

```bash
npx tsc --noEmit
```

Expected: No output (clean type check). If errors appear, fix them before committing.

---

## Task 12: Commit

- [ ] **Step 1: Stage the new files**

```bash
git add webpack.config.js tsconfig.json postcss.config.js tailwind.config.js components.json
git add assets/
git add config/packages/webpack_encore.yaml
git add templates/base.html.twig templates/site/base_site.html.twig templates/admin/base_admin.html.twig templates/site/index.html.twig
git add package.json package-lock.json composer.json composer.lock
git add .gitignore
```

- [ ] **Step 2: Commit**

```bash
git commit -m "$(cat <<'EOF'
Added Webpack Encore + React 18 + TypeScript + Tailwind CSS + shadcn/ui infrastructure

- Replaced Gulp build pipeline with Symfony Webpack Encore
- Added React 18 + TypeScript (Babel-based, Encore entry points per section)
- Configured Tailwind CSS 3 + PostCSS + shadcn/ui with CSS variable theme
- Added smoke-test HelloIsland component on homepage to verify the island pattern
- Kept existing web/css/app.css + web/js/app.js (removed in Phase 4)

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>
EOF
)"
```

---

## Troubleshooting

**`encore_entry_link_tags` / `encore_entry_script_tags` throwing "entrypoints.json not found"**
Run `npm run dev` first to generate `web/build/entrypoints.json`. In production, run `npm run build`.

**Babel error: "Support for the experimental syntax 'jsx' isn't currently enabled"**
The `configureBabel` call in `webpack.config.js` adds `@babel/preset-react` after Encore's defaults. Verify the preset is in `node_modules/@babel/preset-react/`.

**shadcn CLI fails with "Could not detect framework"**
Normal — shadcn is designed for Vite/Next.js. Use the manual `button.tsx` from Task 7 Step 2 instead.

**TypeScript path alias `@/` not resolving in IDE**
The `tsconfig.json` `paths` field handles IDE resolution. The webpack alias in `webpack.config.js` handles build-time resolution. Both must be present.