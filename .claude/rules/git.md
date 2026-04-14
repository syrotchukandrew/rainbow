# Git Rules

## Branching

- `master` — stable releases only, never commit directly
- `dev` — integration branch, merge target for all features
- `feature/name-of-the-feature` — implement every feature in detached branch

## Commits

- Commit only when explicitly asked by the user
- Stage specific files by name — never `git add -A` or `git add .`
- Never commit: `.env.local`, `parameters.yml`, secrets, large binaries
- Write commit messages in an past tense: "Added X", "Fixed Y", "Removed Z"
- Never skip hooks (`--no-verify`)
- Never amend published commits

## Safety

- Never force-push to `master` or `dev`
- Never `git reset --hard` without user confirmation
- Never `git clean -f` without user confirmation
- If unexpected files or branches exist, investigate before deleting

## Pull Requests

- PRs target `dev` (not `master`)
- All tests must pass before opening a PR
- Title: short imperative sentence under 70 characters
- Body: summary of what changed and why

## .gitignore

Project-specific ignores already cover:
- `vendor/`, `var/`, `bin/`
- `.claude/settings.local.json`