# Symfony Rules

## Current Version

Symfony **6.4 LTS** — migrating toward 7.2. Use only APIs available in 6.4.

## Dependency Injection

- Always use constructor injection
- Never `$this->get()`, `$this->container->get()`, or `$this->getDoctrine()`
- Inject `ManagerRegistry` for Doctrine access:
  ```php
  public function __construct(private ManagerRegistry $doctrine) {}
  $em = $this->doctrine->getManager();
  ```

## Controllers

- Extend `AbstractController` (or implement `ServiceSubscriberInterface` if custom)
- Use `#[Route]` attributes with explicit `methods` param — no standalone `@Method`
- Return types: `Response`, `JsonResponse`, or use `#[Template]`
- No business logic in controllers — delegate to services

## Security

- Password hashing: `UserPasswordHasherInterface` (not `UserPasswordEncoderInterface`)
- Auth check: `#[IsGranted('ROLE_USER')]` attribute or `security.yaml` access_control
- Anonymous access: `lazy: true` in firewall (not `anonymous: true`)
- Public routes: `PUBLIC_ACCESS` (not `IS_AUTHENTICATED_ANONYMOUSLY`)

## Services Configuration

- `config/services.yaml` — autowiring and autoconfiguration enabled
- Explicit service definitions only when autowiring is insufficient
- Tag services that implement Symfony interfaces (event subscribers, voters, etc.)

## Console Commands

- Extend `Command`, use `#[AsCommand]` attribute
- Always set description and help text
- Return exit codes: `Command::SUCCESS` / `Command::FAILURE`

## Events & Subscribers

- Prefer `EventSubscriberInterface` over `EventListenerInterface`
- Declare subscribed events in `getSubscribedEvents()` statically
- Keep subscribers focused — one concern per subscriber

## Doctrine

- Entities in `src/Entity/`, repositories in `src/Repository/`
- Use `#[ORM\...]` attributes (not XML or YAML mapping)
- Always use migrations — never `doctrine:schema:update --force` in production
- Flush in services/commands, not in entities

## Configuration

- Environment-specific config in `config/packages/{env}/`
- Secrets via `.env.local` or Symfony Secrets — never hardcoded
- Cache clear required after any config change: `php bin/console cache:clear`

## Forbidden (Removed in SF5+)

- `AdvancedUserInterface`
- `UserPasswordEncoderInterface`
- `security.encoders` config key
- `anonymous: true` firewall option
- `IS_AUTHENTICATED_ANONYMOUSLY`
- `getDoctrine()` in controllers
- `@Method` annotation