---
name: code-reviewer
description: Use when reviewing code changes, pull requests, or modified files in the Rainbow Symfony project for correctness, security, style, and migration compliance
---

# Code Reviewer

## Overview

Review Rainbow project code for correctness, security, Symfony best practices, and migration compliance. The project is mid-migration through Symfony versions (2.8 → 7.2 LTS); reviews must flag deprecated APIs for the current migration step.

## When to Use

- After implementing a feature or bugfix
- Before merging a branch into `dev`
- When asked to review a diff or PR

## Review Checklist

### Correctness
- Logic matches intent; edge cases handled
- No unused variables, dead code, or leftover debug output
- Doctrine queries are safe (no N+1, correct flush/persist usage)

### Security
- No SQL injection — use DQL parameters, never string concatenation
- No XSS — Twig auto-escapes, but watch `|raw`
- No hardcoded credentials or secrets
- Access control annotations/attributes match intended visibility

### Symfony Style
- Controllers are thin — business logic belongs in services
- Services use constructor injection (not `$this->get()` / `$this->container`)
- Routes use `#[Route]` attributes (SF6+) or `@Route` annotations (SF5)
- No `getDoctrine()` — inject `ManagerRegistry` directly
- No `@Method` annotation — use `methods` param in `@Route`

### Migration Compliance
Check against the current step in `CLAUDE.md`. Flag any use of APIs that are removed in the target version:

| Removed in SF5 | Removed in SF6 |
|----------------|----------------|
| `AdvancedUserInterface` | Legacy service locators |
| `UserPasswordEncoderInterface` | Old security voters pattern |
| `security.encoders` config | |
| `anonymous: true` firewall | |
| `IS_AUTHENTICATED_ANONYMOUSLY` | |

### Tests
- All 49 tests must remain green (`php bin/phpunit -c app/`)
- New logic should have corresponding test coverage
- No mocked DB in integration tests

## Output Format

Structure findings by priority:

**Critical** — breaks functionality, security issue, or migration blocker
**Major** — style violation, missing test, deprecated API in use
**Minor** — naming, formatting, cosmetic

List file:line for each finding.