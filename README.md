# Rainbow

A real estate platform built with Symfony, originally created in February 2016.

[![Master](https://travis-ci.org/syrotchukandrew/rainbow.svg?branch=master)](https://travis-ci.org/syrotchukandrew/rainbow)
[![Dev](https://travis-ci.org/syrotchukandrew/rainbow.svg?branch=dev)](https://travis-ci.org/syrotchukandrew/rainbow)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/syrotchukandrew/rainbow/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/syrotchukandrew/rainbow/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/syrotchukandrew/rainbow/badges/quality-score.png?b=dev)](https://scrutinizer-ci.com/g/syrotchukandrew/rainbow/?branch=dev)

[![Code Coverage](https://scrutinizer-ci.com/g/syrotchukandrew/rainbow/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/syrotchukandrew/rainbow/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/syrotchukandrew/rainbow/badges/coverage.png?b=dev)](https://scrutinizer-ci.com/g/syrotchukandrew/rainbow/?branch=dev)

## Requirements

- PHP 8.4+
- Composer
- MySQL / MariaDB

## Tech Stack

- **Symfony 8.0**
- **PHP 8.4**
- **Doctrine ORM**
- **Twig** templating
- **Tailwind CSS**
- **PHPUnit 10.5**

## Setup

```bash
composer install
cp .env .env.local  # configure DATABASE_URL and other env vars
php bin/console doctrine:migrations:migrate
php bin/console cache:clear
```

## Running Tests

```bash
php bin/phpunit -c app/
```

## Branching

| Branch | Purpose |
|--------|---------|
| `master` | Stable releases |
| `dev` | Integration branch — all features merge here |
| `feature/*` | Feature branches cut from `dev` |
