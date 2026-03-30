# Workflow Rules

## General

- Read existing code before modifying anything
- Do not add features beyond what was asked
- Do not refactor surrounding code when fixing a bug
- Do not add comments, docblocks, or type annotations to code you didn't change
- Do not create new files when editing an existing one would suffice
- Do not add comments in places where it is not needed

## Task Execution

1. Understand the requirement fully before writing code
2. Implement the minimal change that satisfies the requirement
3. Clear cache after config changes: `php bin/console cache:clear`
4. Run the test suite: `php bin/phpunit -c app/` — all tests must pass
5. Review the diff before declaring work done

## Branching

- Feature work happens on `feature/` branches cut from `dev`
- Follow the naming pathern: `feature/name-of-feature`
- Merge target is always `dev`, never `master` directly

## Before Claiming Work is Done

- check code style
- Cache cleared
- All tests passed
- check if we have 200 Ok on frontend (html)
- No debug output left in code
- Diff reviewed for unintended changes