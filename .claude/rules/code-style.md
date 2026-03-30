# Code Style Rules

## PHP

- Follow PSR-12 coding standard
- Use strict types: `declare(strict_types=1);` at top of every PHP file
- Type-hint all method parameters and return types
- Use `readonly` properties where applicable (PHP 8.1+)
- Prefer named arguments for clarity when calling functions with multiple bool/null params

## Naming

| Thing | Convention | Example |
|-------|-----------|---------|
| Classes | PascalCase | `UserManager` |
| Methods | camelCase | `findActiveUsers()` |
| Properties | camelCase | `$firstName` |
| Constants | UPPER_SNAKE | `MAX_RETRY_COUNT` |
| Services (YAML) | snake_case | `app.user_manager` |
| Route names | snake_case | `app_user_profile` |
| Twig templates | snake_case dirs + files | `user/profile.html.twig` |

## Classes

- One class per file
- Keep classes focused — single responsibility
- Controllers: only HTTP concerns (request → response)
- Services: business logic, no HTTP/Twig dependencies
- Entities: data + ORM mapping only, minimal logic

## Dependencies

- Always use constructor injection
- No `$this->get('service.id')` or `$this->container->get()`
- No service locator pattern except where Symfony explicitly requires it
- Type-hint interfaces, not concrete classes

## Twig Templates

- Use translation keys, never hardcoded user-visible strings
- Never use `|raw` without explicit justification
- Keep templates thin — logic belongs in controllers/services

## Symfony

### Controllers
- Extend `AbstractController`
- Use `#[Route]` attributes with explicit `methods` param
- Return `Response` or `JsonResponse` — no `echo`, no direct output
- No business logic — delegate to services
- No `$this->getDoctrine()` — inject `ManagerRegistry` via constructor

### Services
- Register via autowiring (`services.yaml` autoconfigure + autowire)
- Constructor injection only — no setter injection, no property injection
- Type-hint interfaces (`UserRepositoryInterface`), not concrete classes
- Stateless where possible — no request/session state stored on the service

### Entities
- ORM mapping via PHP 8 attributes (`#[ORM\...]`) — no XML, no YAML, no docblock annotations
- No business logic beyond simple computed properties
- No Doctrine calls inside entities — flush/persist in services or commands
- All properties private or protected, accessed via getters/setters

### Forms
- One `FormType` class per form
- `data_class` set in `configureOptions()` for entity-bound forms
- Labels use translation keys, not hardcoded strings
- Constraints defined in the entity via `#[Assert\...]`, not in the form

### Security
- Access control via `#[IsGranted]` attribute on controller methods
- Password hashing: `UserPasswordHasherInterface` only
- Voters for complex permission logic — no inline `isGranted()` chains
- Firewalls use `lazy: true` (not `anonymous: true`)

### Translations
- All user-visible strings go through `trans()` or the `{{ 'key'|trans }}` Twig filter
- Translation files live in `translations/` as YAML
- Keys follow dot-notation: `section.subsection.label`

### Commands
- Use `#[AsCommand]` attribute — no `setName()` / `setDescription()` in `configure()`
- Extend `Command`, return `Command::SUCCESS` / `Command::FAILURE`
- No HTTP or Twig dependencies in commands

## General

- No magic numbers — use named constants
- No commented-out code in commits
- No `var_dump`, `dump()`, or `dd()` in commits
- Prefer explicit over clever