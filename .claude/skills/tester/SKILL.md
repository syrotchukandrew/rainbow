---
name: tester
description: Use when writing, running, or debugging tests in the Rainbow project
---

# Tester

## Overview

Testing guide for the Rainbow Symfony project. The suite has **49 tests** — all must pass before any merge.

## Running Tests

```bash
# Run full suite
php bin/phpunit -c app/

# Run a single test file
php bin/phpunit -c app/ tests/AppBundle/SomeTest.php

# Run tests matching a name pattern
php bin/phpunit -c app/ --filter testSomething

# With verbose output
php bin/phpunit -c app/ --verbose
```

## Test Types

| Type | Location | Purpose |
|------|---------|---------|
| Unit | `tests/AppBundle/` | Isolated logic, no DB |
| Functional | `tests/AppBundle/Controller/` | HTTP-level controller tests |
| Integration | `tests/AppBundle/` | Real DB, real services |

## Rules

- **No mocked DB** in integration tests — use a real test database
- Use `WebTestCase` for functional/controller tests
- Use `KernelTestCase` for service integration tests
- Always `parent::tearDown()` / roll back transactions to isolate tests

## Writing a Functional Test

```php
class SomeControllerTest extends WebTestCase
{
    public function testPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/some-url');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }
}
```

## Writing a Unit Test

```php
class SomeServiceTest extends TestCase
{
    public function testDoesTheThing(): void
    {
        $service = new SomeService(/* inject mocks */);
        $result = $service->doTheThing('input');

        $this->assertSame('expected', $result);
    }
}
```

## Test Database

Ensure `app/config/config_test.yml` has a separate DB for tests. Run fixtures if needed:

```bash
php bin/console doctrine:fixtures:load --env=test --no-interaction
```

## Debugging Failing Tests

1. Run with `--verbose` to see full stack traces
2. Check `var/log/test.log` for app-level errors
3. Verify test DB is migrated: `php bin/console doctrine:migrations:migrate --env=test`
4. Isolate: run just the failing test file with `-c app/`

## Coverage (optional)

```bash
php bin/phpunit -c app/ --coverage-html var/coverage/
```

Requires Xdebug or PCOV enabled.