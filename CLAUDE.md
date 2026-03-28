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
4. **Cache clear** — `php app/console cache:clear` (or `bin/console` from SF5+)
5. **Tests** — `php bin/phpunit -c app/` — all 49 must pass
6. **Code review** — review diff for correctness, style, redundancy, security
7. **Post-review fixes** — apply all review feedback
8. **Cache clear + retest** — repeat steps 4–5 after review fixes
9. **Commit** — on the feature branch
10. **Merge** — merge feature branch → `dev`

## Key Notes

- Project uses non-Flex structure (`app/`, `web/`) — keep through all migrations
- Console command: `app/console` until SF5, then `bin/console`
- Test suite: 49 tests, 115 assertions — zero failures required before merge
- PHP platform: bump as required by bundle constraints
- `web/images/estates/` — gitignored, populated by fixtures/seeds only
