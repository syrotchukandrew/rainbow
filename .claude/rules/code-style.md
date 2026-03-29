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

## General

- No magic numbers — use named constants
- No commented-out code in commits
- No `var_dump`, `dump()`, or `dd()` in commits
- Prefer explicit over clever