# Testing Rules

## Non-Negotiable

- All tests must pass before any merge: `php bin/phpunit -c app/` run in Docker container
- Never mock the database in integration tests — use a real test DB
- Never skip or delete tests to make the suite pass

## Test Types

| Type | Base Class | Uses DB? |
|------|-----------|---------|
| Unit | `TestCase` | No |
| Integration | `KernelTestCase` | Yes (real) |
| Functional | `WebTestCase` | Yes (real) |

## Writing Tests

- Test one thing per test method
- Name tests descriptively: `testUserCannotLoginWithWrongPassword`
- Arrange → Act → Assert structure
- Always call `parent::tearDown()` to prevent test pollution
- Roll back or reload fixtures between tests that mutate data

## Functional Tests

```php
$client = static::createClient();
$client->request('GET', '/path');
$this->assertResponseIsSuccessful();
```

- Test HTTP status codes, redirects, and key page content
- Do not assert on full HTML — use `assertSelectorExists` and `assertSelectorTextContains`

## What Must Be Tested

- Every new service method with non-trivial logic
- Every controller action (at minimum: correct status code)
- Every security rule (unauthenticated access returns 401/302)
- Fixtures and data loading logic

## What Not to Test

- Symfony framework internals
- Doctrine mapping correctness (trust the ORM)
- Trivial getters/setters

## Running Specific Tests

```bash
# Single file
php bin/phpunit -c app/ tests/AppBundle/SomeTest.php

# By name pattern
php bin/phpunit -c app/ --filter testSomething
```

## Test Database

- Configured in `app/config/config_test.yml`
- Migrate before running: `php bin/console doctrine:migrations:migrate --env=test`
- Load fixtures if needed: `php bin/console doctrine:fixtures:load --env=test --no-interaction`