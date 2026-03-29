---
name: devops
description: Use when dealing with deployment, environment configuration, server setup, Docker, CI/CD, or infrastructure tasks for the Rainbow project
---

# DevOps

## Overview

Operations guide for the Rainbow Symfony project — environment setup, deployment, and infrastructure concerns.

## Environment Files

| File | Purpose | Committed? |
|------|---------|-----------|
| `.env` | Default env vars (non-secret) | Yes |
| `.env.local` | Local overrides | No (gitignored) |
| `app/config/parameters.yml` | Symfony parameters | No (gitignored) |

Never commit secrets, database passwords, or API keys.

## Cache & Asset Management

```bash
# Clear Symfony cache
php bin/console cache:clear

# Warm cache for production
php bin/console cache:warmup --env=prod

# Install assets (symlinks or copy)
php bin/console assets:install web/

# Clear Doctrine cache
php bin/console doctrine:cache:clear-metadata
php bin/console doctrine:cache:clear-query
php bin/console doctrine:cache:clear-result
```

## Database

```bash
# Run pending migrations
php bin/console doctrine:migrations:migrate

# Check migration status
php bin/console doctrine:migrations:status

# Generate migration from entity diff
php bin/console doctrine:migrations:diff
```

Always run migrations on deployment — never `doctrine:schema:update --force` in production.

## Dependency Management

```bash
# Install (no dev deps in prod)
composer install --no-dev --optimize-autoloader

# Development
composer install
```

## Logs

- Dev log: `var/log/dev.log`
- Prod log: `var/log/prod.log`
- Tail errors: `tail -f var/log/dev.log | grep ERROR`

## Deployment Checklist

1. `composer install --no-dev --optimize-autoloader`
2. `php bin/console doctrine:migrations:migrate --no-interaction`
3. `php bin/console cache:clear --env=prod`
4. `php bin/console cache:warmup --env=prod`
5. `php bin/console assets:install web/`
6. Set correct file permissions on `var/` and `web/`

## File Permissions (Linux)

```bash
# Symfony cache/log dirs must be writable by web server
chmod -R 775 var/cache var/log
chown -R www-data:www-data var/
```