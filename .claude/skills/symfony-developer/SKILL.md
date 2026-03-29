---
name: symfony-developer
description: Use when implementing features, fixing bugs, or making any backend PHP/Symfony changes in the Rainbow project
---

# Symfony Developer

## Overview

Backend development guide for the Rainbow Symfony project. Currently on Symfony 6.4 LTS, migrating toward 7.2. Follow patterns consistent with the current installed version.

## Project Stack

- **Framework:** Symfony 6.4 LTS
- **PHP:** 8.0+
- **ORM:** Doctrine ORM
- **Auth:** Custom `User` entity (FOSUserBundle removed)
- **Templates:** Twig
- **Tests:** PHPUnit 9.5 (`php bin/phpunit -c app/`)

## Key Conventions

### Dependency Injection
Always use constructor injection:
```php
public function __construct(
    private ManagerRegistry $doctrine,
    private UserPasswordHasherInterface $hasher,
) {}
```
Never use `$this->get()`, `$this->container`, or `$this->getDoctrine()`.

### Controllers
- Keep controllers thin — delegate to services
- Use `#[Route]` attributes with `methods` param (no `@Method` annotation)
- Return `Response`, `JsonResponse`, or use `#[Template]`

### Security
- Password hashing: `UserPasswordHasherInterface` (not Encoder)
- Access control: `#[IsGranted]` attribute or `security.yaml` `access_control`
- Use `PUBLIC_ACCESS` instead of `IS_AUTHENTICATED_ANONYMOUSLY`

### Doctrine
- Inject `ManagerRegistry`, get `EntityManager` via `$this->doctrine->getManager()`
- Use repositories for queries; keep them in `src/Repository/`
- Always flush after persist in commands/services; let controllers use `#[ORM\HasLifecycleCallbacks]` sparingly

### Entity Structure
```
src/
  Entity/        # Doctrine entities
  Repository/    # Entity repositories
  Service/       # Business logic
  Controller/    # Thin HTTP layer
  Form/          # Symfony form types
  Security/      # Voters, authenticators
```

## Migration Awareness

Before adding any code, verify the API exists in the current version (6.4). Check `CLAUDE.md` for the list of removed APIs per migration step.

## Workflow

1. Read existing code before modifying
2. Run `php bin/console cache:clear` after config changes
3. Run `php bin/phpunit -c app/` — all 49 tests must pass
4. Use `code-reviewer` skill before finalizing