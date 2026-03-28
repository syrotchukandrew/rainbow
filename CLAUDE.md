# Rainbow — Symfony Migration Roadmap

## Migration Path

| Step | From | To | Branch | Status |
|------|------|----|--------|--------|
| 1 | Symfony 2.8 | 3.4 LTS | `feature/symfony-3.4` | ✅ Done |
| 2 | Symfony 3.4 | 4.4 LTS | `feature/symfony-4.4` | ✅ Done |
| 3 | Symfony 4.4 | 5.4 LTS | `feature/symfony-5.4` | 🔄 Current |
| 4 | Symfony 5.4 | 6.4 LTS | `feature/symfony-6.4` | ⏳ Pending |
| 5 | Symfony 6.4 | 7.2 LTS | `feature/symfony-7.2` | ⏳ Pending |

## Workflow Per Step

1. **Branch** — create `feature/symfony-X.Y` from `dev`
2. **Plan** — identify breaking changes, required bundle upgrades, deprecated APIs to replace
3. **Implement** — apply all changes
4. **Cache clear** — `php bin/console cache:clear` (SF5+ uses `bin/console`)
5. **Tests** — `php bin/phpunit -c app/` — all 49 must pass
6. **Code review** — review diff for correctness, style, redundancy, security
7. **Post-review fixes** — apply all review feedback
8. **Cache clear + retest** — repeat steps 4–5 after review fixes
9. **Commit** — on the feature branch
10. **Merge** — merge feature branch → `dev`

---

## Step 3: Symfony 4.4 → 5.4 LTS + PHP 8.0

### PHP
- Bump platform from `7.2.34` → `8.0` in `composer.json`
- PHP 8.0 is the minimum for Symfony 5.4

### Removed in SF5 — must fix before upgrading

| What | SF4.4 | SF5.4 |
|------|-------|-------|
| `AdvancedUserInterface` | deprecated | **removed** — implement `EquatableInterface` instead |
| `UserPasswordEncoderInterface` | deprecated | **removed** — use `UserPasswordHasherInterface` |
| `security.encoders` config | deprecated | **removed** — use `security.password_hashers` |
| `anonymous: true` firewall option | deprecated | **removed** — use `lazy: true` |
| `IS_AUTHENTICATED_ANONYMOUSLY` | deprecated | **removed** — use `PUBLIC_ACCESS` |
| `logout_on_user_change` | deprecated | **removed** — always on in SF5 |
| `AbstractController::getDoctrine()` | deprecated | **removed** — inject `ManagerRegistry` directly |
| `security.password_encoder` service | deprecated | **removed** — use `security.password_hasher` |
| `@Method` annotation | deprecated | **removed** — use `methods` param in `@Route` |

### FOSUserBundle — must be replaced
- `friendsofsymfony/user-bundle ^2.1` has **no SF5 release** and is abandoned
- `User` entity extends `FOS\UserBundle\Model\User` — needs to become standalone
- Replacement strategy:
  1. Make `User` entity standalone (copy all fields from FOSUserBundle's BaseUser)
  2. Implement `UserInterface`, `PasswordAuthenticatedUserInterface`, `EquatableInterface`
  3. Write custom `UserManager` service for password hashing, role management
  4. Replace FOSUserBundle's registration/reset password controllers with custom ones in `SecurityController`
  5. Remove `FOSUserBundle` from `AppKernel.php` and `composer.json`
  6. Remove `fos_user` config from `config.yml`
  7. Update `security.yml` providers, firewalls

### Bundle version bumps

| Bundle | Current | SF5.4 target |
|--------|---------|--------------|
| `symfony/symfony` | `4.4.*` | `5.4.*` |
| `sensio/framework-extra-bundle` | `^6.1` | `^6.1` (SF5 compat in 6.1+) |
| `knplabs/knp-paginator-bundle` | `^5.0` | `^5.10` |
| `liip/imagine-bundle` | `^2.3` | `^2.6` |
| `knplabs/knp-snappy-bundle` | `^1.7` | `^1.9` |
| `stof/doctrine-extensions-bundle` | `^1.6` | `^1.7` |
| `hwi/oauth-bundle` | `^1.2` | `^1.4` |
| `friendsofsymfony/jsrouting-bundle` | `^2.7` | `^3.2` |
| `php-http/httplug-bundle` | `^1.19` | `^1.19` (unchanged) |
| `mhujer/breadcrumbs-bundle` | `^1.0` | `^1.0` (unchanged) |
| `symfony/swiftmailer-bundle` | `^3.4` | **abandoned** — replace with `symfony/mailer` |
| `phpunit/phpunit` | `^8.5` | `^9.5` (PHP 8 requires PHPUnit 9+) |

### SwiftmailerBundle — must be replaced
- Abandoned, no SF5 support
- Replace with `symfony/mailer` (built into SF5)
- Used only by FOSUserBundle for password reset emails — since FOSUserBundle is being removed, this is handled together

### `security.yml` changes

```yaml
# Remove:
anonymous: true                          → lazy: true
logout_on_user_change: true              → remove (always on)
IS_AUTHENTICATED_ANONYMOUSLY             → PUBLIC_ACCESS
security.encoders:                       → security.password_hashers:
    FOS\UserBundle\Model\UserInterface       AppBundle\Entity\User:
        algorithm: bcrypt                        algorithm: bcrypt
```

### `config.yml` changes
- Remove entire `fos_user:` config block
- Remove `swiftmailer:` config block
- Add `framework.mailer:` if email sending is needed

### `AppController.php` changes
- Remove `security.password_encoder` from `getSubscribedServices()`
- Add `Doctrine\Persistence\ManagerRegistry` injection instead of `getDoctrine()`
- All controllers using `$this->getDoctrine()` → inject `ManagerRegistry` via constructor or `getSubscribedServices()`

### `@Method` annotation removal
- All `@Method({"GET","POST"})` → merge into `@Route(..., methods={"GET","POST"})`
- Affects all 8 controllers

### Ordered implementation steps

1. Replace FOSUserBundle (biggest change — unblocks everything else)
2. Replace SwiftmailerBundle with symfony/mailer
3. Bump `symfony/symfony` to `5.4.*` and PHP platform to `8.0` in composer.json
4. Bump all other bundle versions
5. Fix `security.yml` (encoders → password_hashers, anonymous → lazy, remove logout_on_user_change)
6. Fix `UserPasswordEncoderInterface` → `UserPasswordHasherInterface` in fixtures + AppController
7. Replace all `getDoctrine()` calls → inject `ManagerRegistry`
8. Remove `@Method` annotations — merge into `@Route`
9. Update `access_control`: `IS_AUTHENTICATED_ANONYMOUSLY` → `PUBLIC_ACCESS`
10. Update `AppController::getSubscribedServices()` for changed service IDs
11. Run `composer update`, clear cache, run tests
