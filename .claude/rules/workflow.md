# Workflow Rules

## General

- Read existing code before modifying anything
- Do not add features beyond what was asked
- Do not refactor surrounding code when fixing a bug
- Do not add comments, docblocks, or type annotations to code you didn't change
- Do not create new files when editing an existing one would suffice

## Task Execution

1. Understand the requirement fully before writing code
2. Implement the minimal change that satisfies the requirement
3. Clear cache after config changes: `php bin/console cache:clear`
4. Run the test suite: `php bin/phpunit -c app/` — all 49 tests must pass
5. Review the diff before declaring work done

## Branching

- Feature work happens on `feature/` branches cut from `dev`
- Migrations follow the naming convention in `CLAUDE.md`: `feature/symfony-X.Y`
- Merge target is always `dev`, never `master` directly

## Before Claiming Work is Done

- Cache cleared
- All 49 tests pass
- No debug output left in code
- Diff reviewed for unintended changes