# Symfony 7 Modernization Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove all Symfony 2/3 era artifacts from this Symfony 7.4 project — renaming AppBundle to App, migrating parameters.yaml to .env, moving tests to the standard location, and cleaning up legacy entry points.

**Architecture:** The project is already running Symfony 7.4 but has the legacy AppBundle directory/namespace structure from its Symfony 2 origins. All changes are structural (namespace rename, config updates, file moves) — no business logic changes. Each task builds on the prior, so tasks must run in order.

**Tech Stack:** Symfony 7.4, PHP 8.4, Doctrine ORM 2.x, PHPUnit 10.5, Docker (test commands run inside the container)

---

## File Map

| Task | Files Created | Files Modified | Files Deleted |
|------|--------------|----------------|---------------|
| 1 | `src/App/` (renamed) | `src/Kernel.php`, `composer.json` | `src/App/AppBundle.php` |
| 2 | — | `config/services.yaml`, `config/routes.yaml`, `config/packages/doctrine.yaml`, `config/packages/security.yaml` | — |
| 3 | — | All 7 entity files in `src/App/Entity/` | — |
| 4 | `.env`, `.env.test`, `.env.local.dist` | `config/packages/doctrine.yaml`, `config/packages/framework.yaml`, `config/packages/hwi_oauth.yaml`, `config/services.yaml`, `composer.json` | `config/parameters.yaml` |
| 5 | `tests/` tree | `phpunit.xml.dist` | `src/App/Tests/` |
| 6 | `public/index.php` | `bin/console`, `public/app.php` (temp) | `public/app_dev.php`, `public/config.php`, `public/test.php` |
| 7 | — | — | `web-src/` |

---

### Task 1: Rename AppBundle → App (PHP namespace refactoring)

**Files:**
- Rename: `src/AppBundle/` → `src/App/`
- Delete: `src/App/AppBundle.php`
- Modify: `src/Kernel.php`
- Modify: `composer.json`

**Context:** `src/AppBundle/` has 73 PHP files, all with `namespace AppBundle\*`. The bundle class `AppBundle.php` is a Symfony 2/3 artifact no longer needed. Kernel.php registers it at line 26 (`new AppBundle\AppBundle()`) and has a `date_default_timezone_set('Europe/Kiev')` call at line 14 that should leave Kernel.

After this task, config YAML files will still reference `AppBundle\` — that's expected and gets fixed in Task 2. Do NOT run tests until after Task 2.

- [ ] **Step 1: Rename the directory**

```bash
mv src/AppBundle src/App
```

- [ ] **Step 2: Delete the bundle registration class** (no longer needed in Symfony 7)

```bash
rm src/App/AppBundle.php
```

- [ ] **Step 3: Mass-replace all `AppBundle\` references in PHP files**

This single command replaces every occurrence across all PHP files:

```bash
find src/App -name "*.php" -print0 | xargs -0 sed -i '' 's/AppBundle\\/App\\/g'
```

Verify the replace worked — this count should be 0 (no occurrences remain):

```bash
grep -r "AppBundle\\\\" src/App --include="*.php" | wc -l
```

Expected: `0`

- [ ] **Step 4: Update `src/Kernel.php`**

Replace the entire file content with:

```php
<?php

declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
```

This removes: the AppBundle registration, the custom constructor, and the timezone call. The MicroKernelTrait auto-discovers bundles from `config/bundles.php`.

- [ ] **Step 5: Verify `config/bundles.php` does NOT register AppBundle**

```bash
grep -i "appbundle\|AppBundle" config/bundles.php || echo "clean"
```

Expected: `clean`. If AppBundle appears in bundles.php, remove that line.

- [ ] **Step 6: Update `composer.json` autoload**

Change the `autoload` section from:
```json
"autoload": {
    "psr-4": { "": "src/" },
    "classmap": ["src/Kernel.php"]
},
```

To:
```json
"autoload": {
    "psr-4": { "App\\": "src/App/" },
    "classmap": ["src/Kernel.php"]
},
```

- [ ] **Step 7: Regenerate autoloader**

```bash
docker compose exec php composer dump-autoload
```

Expected: `Generated optimized autoload files` (no errors).

- [ ] **Step 8: Move timezone setting to `public/app.php`** (temporary, until Task 6)

Add `date_default_timezone_set('Europe/Kiev');` at the top of `public/app.php`, right after the `<?php` line:

```php
<?php

date_default_timezone_set('Europe/Kiev');

use Symfony\Component\HttpFoundation\Request;

$loader = require __DIR__.'/../vendor/autoload.php';

$kernel = new Kernel('prod', false);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
```

---

### Task 2: Update YAML config files to App\ namespace

**Files:**
- Modify: `config/services.yaml`
- Modify: `config/routes.yaml`
- Modify: `config/packages/doctrine.yaml`
- Modify: `config/packages/security.yaml`

**Context:** After Task 1, all PHP files use `App\` namespace but all YAML configs still reference `AppBundle\`. This task fixes that. The `parameters.yaml` import stays in services.yaml until Task 4. After this task, the test suite must pass.

- [ ] **Step 1: Replace `config/services.yaml`**

```yaml
imports:
    - { resource: parameters.yaml }

services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false

    App\:
        resource: '../src/App'
        exclude:
            - '../src/App/Entity'
            - '../src/App/Form'
            - '../src/App/Repository'
            - '../src/App/Tests'
            - '../src/App/DataFixtures'

    App\DataFixtures\ORM\Dev\:
        resource: '../src/App/DataFixtures/ORM/Dev'
        tags: ['doctrine.fixture.orm']

    App\DataFixtures\ORM\Deploy\:
        resource: '../src/App/DataFixtures/ORM/Deploy'
        tags: ['doctrine.fixture.orm']

    App\Controller\:
        resource: '../src/App/Controller'
        public: true

    my.custom.user_provider:
        alias: App\Security\Core\User\OAuthUserProvider
        public: true

    App\EventListener\RedirectToPreferredLocaleListener:
        bind:
            $locales: '%app_locales%'
            $defaultLocale: '%locale%'

    App\Twig\AppExtension:
        bind:
            $locales: '%app_locales%'

    App\Utils\BreadcrumpsMaker:
        arguments:
            - '@white_october_breadcrumbs'

    App\Controller\SiteController:
        public: true
        bind:
            $breadcrumbs: '@white_october_breadcrumbs'
            $pdf: '@knp_snappy.pdf'
```

- [ ] **Step 2: Replace `config/routes.yaml`**

The `@AppBundle/Controller/` bundle resource syntax requires a registered bundle class. Since we deleted AppBundle.php, switch to direct file paths:

```yaml
app_admin:
    resource: '../src/App/Controller/Admin/'
    type:     attribute

app_api:
    resource: '../src/App/Controller/Api/'
    type:     attribute

app:
    resource: '../src/App/Controller/'
    type:     attribute
    prefix:   /{_locale}
    requirements:
        _locale: "%app_locales%"
    defaults:
        _locale: "%locale%"
    exclude:
        - '../src/App/Controller/Admin/'
        - '../src/App/Controller/Api/'

homepage:
    path:     /{_locale}
    defaults: { _controller: App\Controller\SiteController::indexAction, _locale: uk }
    requirements:
        _locale: uk|en
```

- [ ] **Step 3: Update `config/packages/doctrine.yaml` mapping section only**

Change only the `mappings` section (keep `%database_host%` params — those migrate in Task 4):

```yaml
doctrine:
    dbal:
        driver:   pdo_mysql
        host:     "%database_host%"
        port:     "%database_port%"
        dbname:   "%database_name%"
        user:     "%database_user%"
        password: "%database_password%"
        charset:  UTF8
    orm:
        auto_generate_proxy_classes: "%kernel.debug%"
        naming_strategy: doctrine.orm.naming_strategy.underscore
        mappings:
            App:
                type: attribute
                dir: '%kernel.project_dir%/src/App/Entity'
                prefix: 'App\Entity'
                alias: App
```

- [ ] **Step 4: Update `config/packages/security.yaml`**

Change only the class references (leave everything else as-is):

```bash
sed -i '' 's/AppBundle\\Entity\\User/App\\Entity\\User/g' config/packages/security.yaml
```

Verify:
```bash
grep "AppBundle" config/packages/security.yaml || echo "clean"
```

Expected: `clean`

- [ ] **Step 5: Clear cache and run tests**

```bash
docker compose exec php php bin/console cache:clear
docker compose exec php php bin/phpunit
```

Expected: All 97 tests pass.

- [ ] **Step 6: Check for any remaining AppBundle references in config**

```bash
grep -r "AppBundle" config/ || echo "clean"
```

Expected: `clean`

- [ ] **Step 7: Commit**

```bash
git add src/App/ src/Kernel.php composer.json config/services.yaml config/routes.yaml config/packages/doctrine.yaml config/packages/security.yaml public/app.php
git commit -m "Renamed AppBundle to App: namespace refactoring and config updates"
```

---

### Task 3: Modernize Doctrine ORM attributes (string → ::class)

**Files:**
- Modify: `src/App/Entity/Estate.php`
- Modify: `src/App/Entity/Category.php`
- Modify: `src/App/Entity/Comment.php`
- Modify: `src/App/Entity/District.php`
- Modify: `src/App/Entity/File.php`
- Modify: `src/App/Entity/User.php`
- Modify: `src/App/Entity/MenuItem.php`

**Context:** After Task 1's mass replace, ORM attributes use string class refs like `targetEntity: 'App\Repository\EstateRepository'`. Doctrine supports `::class` constants which are refactor-safe and IDE-friendly. Each entity needs `use` imports added and string literals replaced.

**Special case — `Estate.php`:** It has `use Symfony\Component\HttpFoundation\File\File;` which conflicts with `App\Entity\File`. The entity File must use an alias: `use App\Entity\File as EstateFile;`. The `$mainFoto` property type (`?\App\Entity\File`) must also change to `?EstateFile`.

- [ ] **Step 1: Write a failing test to verify ORM mapping is intact**

Run the schema validator (which fails if entity mapping is broken):

```bash
docker compose exec php php bin/console doctrine:schema:validate --skip-sync
```

Expected: `[OK] The mapping files are correct.`

If this fails before any edits, investigate before continuing.

- [ ] **Step 2: Update `src/App/Entity/Estate.php`**

Add these `use` statements (after existing uses):
```php
use App\Entity\File as EstateFile;
use App\Repository\EstateRepository;
use App\Entity\District;
use App\Entity\Category;
use App\Entity\Comment;
```

Remove: `use Symfony\Component\HttpFoundation\File\File;` (if only used for the property type — verify no other usage first).

Change ORM attributes:
```php
// Line 15: repositoryClass
#[ORM\Entity(repositoryClass: EstateRepository::class)]

// Line 71: district ManyToOne
#[ORM\ManyToOne(targetEntity: District::class, inversedBy: 'estates')]

// Line 75: files OneToMany
#[ORM\OneToMany(targetEntity: EstateFile::class, mappedBy: 'estate', cascade: ['remove'], orphanRemoval: true)]

// Line 78: mainFoto OneToOne
#[ORM\OneToOne(targetEntity: EstateFile::class)]

// Line 83: comments OneToMany
#[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'estate', orphanRemoval: true)]

// Line 87: category ManyToOne
#[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'estates')]
```

Change property type (line 79):
```php
private ?EstateFile $mainFoto = null;
```

Change collection type in `$files` (line 76):
```php
private Collection $files;  // stays Collection, no change needed
```

- [ ] **Step 3: Update `src/App/Entity/Category.php`**

Add:
```php
use App\Entity\Estate;
use App\Repository\CategoryRepository;
```

Change:
```php
#[ORM\Entity(repositoryClass: CategoryRepository::class)]

// self-referencing relations use Category::class
#[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'children')]
#[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'parent')]

#[ORM\OneToMany(targetEntity: Estate::class, mappedBy: 'category')]
```

- [ ] **Step 4: Update `src/App/Entity/Comment.php`**

Add:
```php
use App\Entity\Estate;
use App\Repository\CommentRepository;
```

Change:
```php
#[ORM\Entity(repositoryClass: CommentRepository::class)]

#[ORM\ManyToOne(targetEntity: Estate::class, inversedBy: 'comments', cascade: ['persist'])]
```

- [ ] **Step 5: Update `src/App/Entity/District.php`**

Add:
```php
use App\Entity\Estate;
use App\Repository\DistrictRepository;
```

Change:
```php
#[ORM\Entity(repositoryClass: DistrictRepository::class)]

#[ORM\OneToMany(targetEntity: Estate::class, mappedBy: 'district')]
```

- [ ] **Step 6: Update `src/App/Entity/File.php`**

Add:
```php
use App\Entity\Estate;
```

Change:
```php
#[ORM\ManyToOne(targetEntity: Estate::class, inversedBy: 'files', cascade: ['persist'])]
```

(`File.php` has no repository, so no `repositoryClass` to change.)

- [ ] **Step 7: Update `src/App/Entity/User.php`**

Add:
```php
use App\Entity\Estate;
use App\Repository\UserRepository;
```

Change:
```php
#[ORM\Entity(repositoryClass: UserRepository::class)]

#[ORM\ManyToMany(targetEntity: Estate::class)]
```

- [ ] **Step 8: Update `src/App/Entity/MenuItem.php`**

Add:
```php
use App\Repository\MenuItemRepository;
```

Change:
```php
#[ORM\Entity(repositoryClass: MenuItemRepository::class)]
```

(`MenuItem.php` has no relations to other app entities.)

- [ ] **Step 9: Verify ORM mapping still valid**

```bash
docker compose exec php php bin/console doctrine:schema:validate --skip-sync
```

Expected: `[OK] The mapping files are correct.`

- [ ] **Step 10: Verify no string class refs remain in entities**

```bash
grep -n "targetEntity: '" src/App/Entity/*.php
grep -n "repositoryClass: '" src/App/Entity/*.php
```

Expected: no output (all string refs replaced with ::class).

- [ ] **Step 11: Run tests**

```bash
docker compose exec php php bin/phpunit
```

Expected: All 97 tests pass.

- [ ] **Step 12: Commit**

```bash
git add src/App/Entity/
git commit -m "Modernized Doctrine ORM attributes: replaced string class refs with ::class constants"
```

---

### Task 4: Migrate `config/parameters.yaml` to `.env`

**Files:**
- Create: `.env`
- Create: `.env.test`
- Create: `.env.local.dist`
- Modify: `config/packages/doctrine.yaml`
- Modify: `config/packages/framework.yaml`
- Modify: `config/packages/hwi_oauth.yaml`
- Modify: `config/services.yaml` (remove parameters.yaml import)
- Modify: `composer.json` (remove incenteev)
- Delete: `config/parameters.yaml`

**Context:** `config/parameters.yaml` is a Symfony 2/3 artifact. Modern Symfony uses `.env` files. The `incenteev/composer-parameter-handler` package auto-generates `parameters.yaml` during `composer install` — both must be removed together. The doctrine YAML also switches from separate host/port/name params to a single `DATABASE_URL`. The `locale` and `app_locales` parameters are app-specific (not secret/env-specific) and stay as YAML parameters in `framework.yaml`.

- [ ] **Step 1: Create `.env`**

```
APP_ENV=dev
APP_DEBUG=1
APP_SECRET=ThisTokenIsNotSoSecretChangeIt

DATABASE_URL="mysql://root:root@db:3306/rainbow?serverVersion=8.0"

MAILER_DSN=null://null
MAILER_FROM_ADDRESS=noreply@example.com
MAILER_FROM_NAME=Rainbow

FACEBOOK_CLIENT_ID=0
FACEBOOK_CLIENT_SECRET=0
VKONTAKTE_CLIENT_ID=0
VKONTAKTE_CLIENT_SECRET=0
GOOGLE_CLIENT_ID=0
GOOGLE_CLIENT_SECRET=0
```

- [ ] **Step 2: Create `.env.test`**

This file is loaded in the `test` environment and overrides `.env`:

```
APP_ENV=test
APP_DEBUG=1
```

The test database connection is already configured separately (check `config/packages/test/` — if doctrine is overridden there, leave it; if not, the test DB must be reflected in the DATABASE_URL or a `config/packages/test/doctrine.yaml` override).

- [ ] **Step 3: Create `.env.local.dist`** (template for developers, not committed as `.env.local`)

```
# Copy this file to .env.local and fill in real values for local development
# DATABASE_URL="mysql://root:password@127.0.0.1:3306/rainbow?serverVersion=8.0"
# APP_SECRET=generate-a-real-secret-here
# FACEBOOK_CLIENT_ID=your-app-id
# FACEBOOK_CLIENT_SECRET=your-app-secret
```

- [ ] **Step 4: Update `config/packages/doctrine.yaml`**

Replace the entire file:

```yaml
doctrine:
    dbal:
        url: '%env(resolve:DATABASE_URL)%'
        charset: UTF8
    orm:
        auto_generate_proxy_classes: '%kernel.debug%'
        naming_strategy: doctrine.orm.naming_strategy.underscore
        mappings:
            App:
                type: attribute
                dir: '%kernel.project_dir%/src/App/Entity'
                prefix: 'App\Entity'
                alias: App
```

- [ ] **Step 5: Update `config/packages/framework.yaml`**

Replace `%secret%` and `%mailer_dsn%` with env vars. Keep `locale` and `app_locales` as parameters (they are not environment-specific):

```yaml
parameters:
    locale: uk
    app_locales: 'uk|en'

framework:
    translator:
        fallbacks: ["%locale%"]
    secret: '%env(APP_SECRET)%'
    mailer:
        dsn: '%env(MAILER_DSN)%'
    router:
        strict_requirements: ~
    assets: ~
    form: ~
    csrf_protection: ~
    validation: { enable_attributes: true }
    default_locale: "%locale%"
    trusted_hosts: ~
    session:
        storage_factory_id: session.storage.factory.native
    fragments: ~
```

- [ ] **Step 6: Update `config/packages/hwi_oauth.yaml`**

Replace the `%param%` refs with `%env()%` refs:

```yaml
hwi_oauth:
    connect:
        account_connector: my.custom.user_provider
    firewall_names: [main]
    resource_owners:
        facebook:
            type:                facebook
            client_id:           "%env(FACEBOOK_CLIENT_ID)%"
            client_secret:       "%env(FACEBOOK_CLIENT_SECRET)%"
            options:
                display:    popup
            csrf:                true
            scope:               "email"
            infos_url:     "https://graph.facebook.com/me?fields=id,name,email,picture.type(square)"
            paths:
                email:          email
                profilepicture: picture.data.url

        google:
            type:         google
            client_id:    "%env(GOOGLE_CLIENT_ID)%"
            client_secret: "%env(GOOGLE_CLIENT_SECRET)%"
            scope:        "https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile"
            paths:
                email: email

        vkontakte:
            type:          vkontakte
            client_id:     "%env(VKONTAKTE_CLIENT_ID)%"
            client_secret: "%env(VKONTAKTE_CLIENT_SECRET)%"
            scope:         email status
```

- [ ] **Step 7: Remove `parameters.yaml` import from `config/services.yaml`**

Delete this block from the top of the file:

```yaml
imports:
    - { resource: parameters.yaml }
```

- [ ] **Step 8: Remove `incenteev/composer-parameter-handler` from `composer.json`**

Make these changes to `composer.json`:

1. Remove from `require`:
   ```
   "incenteev/composer-parameter-handler": "~2.0",
   ```

2. Remove the entire `scripts` section:
   ```json
   "scripts": {
       "post-install-cmd": ["Incenteev\\ParameterHandler\\ScriptHandler::buildParameters"],
       "post-update-cmd":  ["Incenteev\\ParameterHandler\\ScriptHandler::buildParameters"]
   },
   ```

3. Remove from `extra`:
   ```json
   "symfony-web-dir": "public",
   "incenteev-parameters": { "file": "config/parameters.yaml" }
   ```

- [ ] **Step 9: Delete `config/parameters.yaml`**

```bash
rm config/parameters.yaml
```

- [ ] **Step 10: Run `composer update` to remove the incenteev package**

```bash
docker compose exec php composer update incenteev/composer-parameter-handler --with-all-dependencies
```

Or if removing it completely:

```bash
docker compose exec php composer remove incenteev/composer-parameter-handler
```

- [ ] **Step 11: Clear cache and run tests**

```bash
docker compose exec php php bin/console cache:clear
docker compose exec php php bin/phpunit
```

Expected: All 97 tests pass. If doctrine connection fails, check that `DATABASE_URL` in `.env.test` or `config/packages/test/doctrine.yaml` points to the test database.

- [ ] **Step 12: Verify no old %param% references remain in configs**

```bash
grep -r "database_host\|database_port\|database_name\|database_user\|database_password\|mailer_dsn\|facebook_client_id\|google_client_id\|vkontakte_client_id" config/ || echo "clean"
```

Expected: `clean`

- [ ] **Step 13: Commit**

```bash
git add .env .env.test .env.local.dist config/packages/doctrine.yaml config/packages/framework.yaml config/packages/hwi_oauth.yaml config/services.yaml composer.json composer.lock
git rm config/parameters.yaml
git commit -m "Migrated parameters.yaml to .env: removed incenteev handler and all %param% references"
```

---

### Task 5: Move tests to `tests/` root directory

**Files:**
- Create: `tests/Controller/Admin/`, `tests/Controller/Api/`, `tests/Controller/`, `tests/Form/`
- Move: `src/App/Tests/**` → `tests/**`
- Modify: namespaces in all test files
- Modify: `phpunit.xml.dist`
- Delete: `src/App/Tests/`

**Context:** Symfony 7 convention puts tests in `tests/` at the project root, with namespace `App\Tests\`. Currently they live in `src/App/Tests/` (after Task 1's rename) with namespace `App\Tests\Controller\Admin` etc. The `phpunit.xml.dist` also has wrong schema URL (PHPUnit 9.5 schema but we have PHPUnit 10.5).

**Test files to move:**
- `src/App/Tests/Controller/Admin/AdminCategoryControllerTest.php` → `tests/Controller/Admin/`
- `src/App/Tests/Controller/Admin/AdminCommentControllerTest.php` → `tests/Controller/Admin/`
- `src/App/Tests/Controller/Admin/AdminDistrictControllerTest.php` → `tests/Controller/Admin/`
- `src/App/Tests/Controller/Admin/AdminEstateControllerTest.php` → `tests/Controller/Admin/`
- `src/App/Tests/Controller/Admin/AdminMenuItemControllerTest.php` → `tests/Controller/Admin/`
- `src/App/Tests/Controller/Admin/UserControllerTest.php` → `tests/Controller/Admin/`
- `src/App/Tests/Controller/Admin/AdminApiControllerTest.php` → `tests/Controller/Api/`
- `src/App/Tests/Controller/Admin/PublicEstateApiControllerTest.php` → `tests/Controller/Api/`
- `src/App/Tests/Controller/BaseTestController.php` → `tests/Controller/`
- `src/App/Tests/Controller/SiteControllerTest.php` → `tests/Controller/`
- `src/App/Tests/Form/CategoryTypeTest.php` → `tests/Form/`
- `src/App/Tests/Form/CommentTypeTest.php` → `tests/Form/`
- `src/App/Tests/Form/DistrictTypeTest.php` → `tests/Form/`
- `src/App/Tests/Form/FloorTypeTest.php` → `tests/Form/`
- `src/App/Tests/Form/MenuItemTypeTest.php` → `tests/Form/`
- `src/App/Tests/Form/UserTypeTest.php` → `tests/Form/`

- [ ] **Step 1: Create the `tests/` directory structure and move files**

```bash
mkdir -p tests/Controller/Admin tests/Controller/Api tests/Form

# Admin controller tests
mv src/App/Tests/Controller/Admin/AdminCategoryControllerTest.php tests/Controller/Admin/
mv src/App/Tests/Controller/Admin/AdminCommentControllerTest.php tests/Controller/Admin/
mv src/App/Tests/Controller/Admin/AdminDistrictControllerTest.php tests/Controller/Admin/
mv src/App/Tests/Controller/Admin/AdminEstateControllerTest.php tests/Controller/Admin/
mv src/App/Tests/Controller/Admin/AdminMenuItemControllerTest.php tests/Controller/Admin/
mv src/App/Tests/Controller/Admin/UserControllerTest.php tests/Controller/Admin/

# API controller tests (they're in Admin/ subdir after Task 1)
mv src/App/Tests/Controller/Admin/AdminApiControllerTest.php tests/Controller/Api/
mv src/App/Tests/Controller/Admin/PublicEstateApiControllerTest.php tests/Controller/Api/

# Base and site tests
mv src/App/Tests/Controller/BaseTestController.php tests/Controller/
mv src/App/Tests/Controller/SiteControllerTest.php tests/Controller/

# Form tests
mv src/App/Tests/Form/CategoryTypeTest.php tests/Form/
mv src/App/Tests/Form/CommentTypeTest.php tests/Form/
mv src/App/Tests/Form/DistrictTypeTest.php tests/Form/
mv src/App/Tests/Form/FloorTypeTest.php tests/Form/
mv src/App/Tests/Form/MenuItemTypeTest.php tests/Form/
mv src/App/Tests/Form/UserTypeTest.php tests/Form/

# Remove the now-empty Tests directory
rm -rf src/App/Tests/
```

- [ ] **Step 2: Update namespaces in all test files**

The namespace changes from `App\Tests\Controller\Admin` → `App\Tests\Controller\Admin` (the logical namespace stays the same, only the physical directory changes). PHPUnit finds tests by directory scan, not namespace. But the autoload must be updated too.

Mass replace namespace prefix in test files (the namespace in the files already says `App\Tests\...` after Task 1's mass replace):

```bash
grep -r "namespace App" tests/ | head -5
```

The namespaces should already read `App\Tests\Controller\Admin` etc. No change needed to the `namespace` declarations.

Verify:
```bash
grep -rn "^namespace" tests/
```

Expected output (all should start with `App\Tests\`):
```
tests/Controller/Admin/AdminCategoryControllerTest.php:namespace App\Tests\Controller\Admin;
tests/Controller/Admin/AdminCommentControllerTest.php:namespace App\Tests\Controller\Admin;
...
tests/Form/CategoryTypeTest.php:namespace App\Tests\Form;
```

- [ ] **Step 3: Add `tests/` to `composer.json` autoload-dev**

Add an `autoload-dev` section to `composer.json` (if it doesn't exist):

```json
"autoload-dev": {
    "psr-4": {
        "App\\Tests\\": "tests/"
    }
}
```

Then regenerate autoloader:

```bash
docker compose exec php composer dump-autoload
```

- [ ] **Step 4: Remove `src/App/Tests` from services.yaml exclusion list**

In `config/services.yaml`, remove this line from the `exclude` list under `App\:`:

```yaml
            - '../src/App/Tests'
```

- [ ] **Step 5: Replace `phpunit.xml.dist`**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.5/phpunit.xsd"
         colors="true"
         bootstrap="vendor/autoload.php">
    <php>
        <ini name="error_reporting" value="-1"/>
        <env name="KERNEL_CLASS" value="Kernel"/>
    </php>
    <testsuites>
        <testsuite name="Project Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>src/App</directory>
        </include>
        <exclude>
            <directory>src/App/DataFixtures</directory>
        </exclude>
    </source>
</phpunit>
```

- [ ] **Step 6: Run tests with updated config**

```bash
docker compose exec php php bin/phpunit
```

Expected: All 97 tests pass (now found via `tests/` instead of `src/App/Tests/`).

- [ ] **Step 7: Commit**

```bash
git add tests/ phpunit.xml.dist composer.json composer.lock config/services.yaml
git rm -r src/App/Tests/
git commit -m "Moved tests from src/App/Tests/ to tests/ and updated phpunit.xml.dist to PHPUnit 10.5 schema"
```

---

### Task 6: Modernize `bin/console` and public entry point

**Files:**
- Modify: `bin/console`
- Create: `public/index.php`
- Modify: `composer.json` (add `symfony/runtime`)
- Delete: `public/app_dev.php`, `public/config.php`, `public/test.php`

**Context:** `bin/console` uses legacy `SYMFONY_ENV`/`SYMFONY_DEBUG` env vars (Symfony 2/3) instead of `APP_ENV`/`APP_DEBUG`. The project has no `public/index.php` — instead it has `public/app.php` (prod) and `public/app_dev.php` (dev). Modern Symfony uses a single entry point that reads `APP_ENV` from `.env`. The `symfony/runtime` package is required for `autoload_runtime.php`.

- [ ] **Step 1: Add `symfony/runtime` to `composer.json`**

```bash
docker compose exec php composer require symfony/runtime
```

Expected: Package installed. `vendor/autoload_runtime.php` now exists.

- [ ] **Step 2: Replace `bin/console`**

```php
#!/usr/bin/env php
<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    return new Application($kernel);
};
```

Make it executable:

```bash
chmod +x bin/console
```

- [ ] **Step 3: Create `public/index.php`**

```php
<?php

date_default_timezone_set('Europe/Kiev');

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
```

- [ ] **Step 4: Remove timezone from `public/app.php`** (legacy file still used as a fallback for now — clean it up before deleting)

Remove the `date_default_timezone_set` line that was added in Task 1, Step 8.

- [ ] **Step 5: Delete legacy entry points**

```bash
rm public/app_dev.php public/config.php public/test.php
```

(`public/app.php` is kept for now — web servers may still reference it. It can be removed once the web server is updated to point to `index.php`.)

- [ ] **Step 6: Verify `bin/console` works**

```bash
docker compose exec php php bin/console list
```

Expected: Symfony command list displayed. No errors.

- [ ] **Step 7: Clear cache and run tests**

```bash
docker compose exec php php bin/console cache:clear
docker compose exec php php bin/phpunit
```

Expected: All 97 tests pass.

- [ ] **Step 8: Commit**

```bash
git add bin/console public/index.php composer.json composer.lock
git rm public/app_dev.php public/config.php public/test.php
git commit -m "Modernized bin/console to use APP_ENV/APP_DEBUG and added public/index.php entry point"
```

---

### Task 7: Delete legacy frontend assets (`web-src/`)

**Files:**
- Delete: `web-src/` (entire directory)

**Context:** `web-src/` contains the Bootstrap 3 era source files (`css/`, `fonts/`, `images/`, `js/`, `less/`). The React + Tailwind migration (Phase 4) is complete — these files have been replaced by `assets/` (Webpack Encore). Nothing in the codebase imports from `web-src/`. Deleting it is safe.

- [ ] **Step 1: Verify nothing references `web-src/`**

```bash
grep -r "web-src" . --include="*.php" --include="*.yaml" --include="*.twig" --include="*.json" --include="*.ts" --include="*.tsx" || echo "no references"
```

Expected: `no references`. If any references appear, investigate before deleting.

- [ ] **Step 2: Delete `web-src/`**

```bash
rm -rf web-src/
```

- [ ] **Step 3: Run tests to confirm nothing broke**

```bash
docker compose exec php php bin/phpunit
```

Expected: All 97 tests pass.

- [ ] **Step 4: Commit**

```bash
git rm -r web-src/
git commit -m "Deleted web-src/ legacy Bootstrap 3 source directory (superseded by assets/ with Encore)"
```

---

## Self-Review Checklist

**Spec coverage:**
- [x] Task 1: AppBundle → App directory rename + PHP namespace mass replace
- [x] Task 2: YAML config files updated (services, routes, doctrine, security)
- [x] Task 3: Doctrine ORM attributes: string refs → ::class
- [x] Task 4: parameters.yaml → .env migration + incenteev removal
- [x] Task 5: Tests moved to tests/, phpunit.xml.dist modernized to PHPUnit 10.5 schema
- [x] Task 6: bin/console modernized (SYMFONY_ENV → APP_ENV), public/index.php created, legacy php files deleted
- [x] Task 7: web-src/ deleted

**Not in scope (deferred):**
- Removing `Action` suffix from controller methods (e.g., `indexAction` → `index`) — cosmetic, non-breaking
- Moving `src/App/` contents directly into `src/` (standard Symfony 7 layout) — bigger disruption, same functional result
- Removing `public/app.php` — requires web server config update outside this plan
