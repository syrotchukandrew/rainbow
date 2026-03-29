# Git Rules

## Branching

- `master` — stable releases only, never commit directly
- `dev` — integration branch, merge target for all features
- `feature/symfony-X.Y` — Symfony migration steps
- `feature/<name>` or `fix/<name>` — other work

## Commits

- Commit only when explicitly asked by the user
- Stage specific files by name — never `git add -A` or `git add .`
- Never commit: `.env.local`, `parameters.yml`, secrets, large binaries
- Write commit messages in imperative mood: "Add X", "Fix Y", "Remove Z"
- Never skip hooks (`--no-verify`)
- Never amend published commits

## Safety

- Never force-push to `master` or `dev`
- Never `git reset --hard` without user confirmation
- Never `git clean -f` without user confirmation
- If unexpected files or branches exist, investigate before deleting

## Pull Requests

- PRs target `dev` (not `master`)
- All 49 tests must pass before opening a PR
- Title: short imperative sentence under 70 characters
- Body: summary of what changed and why

## .gitignore

Project-specific ignores already cover:
- `vendor/`, `var/`, `bin/`
- `app/config/parameters.yml`
- `web/media/`, `web/images/estates/`
- `.claude/settings.local.json`