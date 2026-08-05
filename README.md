# Ashraf Beshtawi — Portfolio

> A database-driven personal portfolio with a built-in control panel. Symfony 7.4 · PostgreSQL 18 · Docker.

[![CI](https://github.com/ashrafbeshtawi/Portfolio/actions/workflows/ci.yaml/badge.svg)](https://github.com/ashrafbeshtawi/Portfolio/actions/workflows/ci.yaml)
[![Docker image](https://github.com/ashrafbeshtawi/Portfolio/actions/workflows/docker-publish.yaml/badge.svg)](https://github.com/ashrafbeshtawi/Portfolio/actions/workflows/docker-publish.yaml)
![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)
![Symfony 7.4](https://img.shields.io/badge/Symfony-7.4-000000?logo=symfony)
![PostgreSQL 18](https://img.shields.io/badge/PostgreSQL-18-4169E1?logo=postgresql&logoColor=white)

## What is this?

My personal portfolio site, rebuilt as a server-rendered Symfony application. All
content — profile, skills, certifications and projects — lives in PostgreSQL and is
editable through a password-protected control panel at `/admin`, so the site can be
updated without touching code or redeploying. Every push to the default branch
publishes a multi-arch (amd64 + arm64) production image to GitHub Container Registry.

## Tech stack

| Layer      | Choice                                             |
|------------|----------------------------------------------------|
| Framework  | Symfony 7.4 (PHP 8.4)                              |
| Database   | PostgreSQL 18 + Doctrine ORM & Migrations          |
| Templating | Twig                                               |
| Auth       | Single admin user from `ADMIN_USERNAME` / `ADMIN_PASSWORD` env vars |
| Containers | Docker (PHP-FPM + Nginx in one image), docker-compose for local dev |
| Tests      | PHPUnit (unit + integration) · Symfony Panther + Chromium (E2E) |
| CI/CD      | GitHub Actions: test suite on every push/PR, image publish to GHCR on `main`/`master` |

## Quickstart

```bash
git clone https://github.com/ashrafbeshtawi/Portfolio.git
cd Portfolio
docker compose up -d
```

That's it. The first boot installs Composer dependencies, waits for PostgreSQL, runs
the migrations and seeds the initial content.

- Site: http://localhost:8080
- Control panel: http://localhost:8080/admin (dev credentials `admin` / `admin`,
  override with `ADMIN_USERNAME` / `ADMIN_PASSWORD` env vars)

Database data survives restarts — it lives in the named volume `db_data`.

## Running the production image

The CI pipeline publishes `ghcr.io/ashrafbeshtawi/portfolio` for `linux/amd64` and
`linux/arm64`. The container is configured entirely through environment variables
(see [.env.example](.env.example)):

```bash
docker run -p 80:80 \
  -e APP_SECRET="$(php -r 'echo bin2hex(random_bytes(16));')" \
  -e DATABASE_URL="postgresql://user:pass@db-host:5432/portfolio?serverVersion=18&charset=utf8" \
  -e ADMIN_USERNAME=admin \
  -e ADMIN_PASSWORD=a-strong-password \
  ghcr.io/ashrafbeshtawi/portfolio:latest
```

On boot the container runs pending migrations, seeds the database if it is empty, and
**refuses to start with a missing or default admin password**.

## Running tests

```bash
docker compose exec app bin/console doctrine:database:create --env=test --if-not-exists
docker compose exec app bin/console doctrine:migrations:migrate --env=test -n

docker compose exec app vendor/bin/phpunit --testsuite unit          # unit
docker compose exec app vendor/bin/phpunit --testsuite integration   # integration
docker compose exec app vendor/bin/phpunit -c phpunit.e2e.xml        # E2E (Chromium)
```

## Project structure

```
├── config/               # Symfony + Doctrine + security configuration
├── docker/
│   ├── php/              # App image: Dockerfile (dev/prod), entrypoint, supervisord
│   └── nginx/            # Nginx vhost (proxies to PHP-FPM in the same container)
├── migrations/           # Doctrine migrations
├── public/               # Web root: index.php, CSS, images, CV
├── src/
│   ├── Controller/       # Public site + /admin control panel
│   ├── Entity/           # Profile, SkillCategory, Skill, ProjectGroup, Project
│   ├── Form/             # Admin form types
│   ├── Security/         # Env-var-based authenticator + user provider
│   └── Service/          # ContentSeeder (initial content)
├── templates/            # Twig: public page, login, admin panel
├── tests/
│   ├── Unit/             # Pure PHP tests
│   ├── Integration/      # WebTestCase tests (transaction-isolated via DAMA)
│   └── E2E/              # Panther + Chromium browser tests
└── .github/workflows/    # ci.yaml (tests) · docker-publish.yaml (GHCR, multi-arch)
```

## Conventions & contributing

- Every change ships with tests — see [CHEATSHEET.md](CHEATSHEET.md) for all commands.
- Content changes don't need code: use the control panel.
- Schema changes: edit the entities, then `bin/console doctrine:migrations:diff`.

## License

© Ashraf Beshtawi. All rights reserved.
