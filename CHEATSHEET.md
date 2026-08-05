# Cheatsheet

Command reference. All `bin/console`, `composer` and `phpunit` commands run **inside the app container** unless noted.

## Docker

- `docker compose up -d` — start the stack (auto: composer install, migrations, seed)
- `docker compose down` — stop the stack (DB data kept in the `db_data` volume)
- `docker compose down -v` — stop and **wipe the database volume**
- `docker compose build app` — rebuild the dev image (after Dockerfile changes)
- `docker compose logs -f app` — follow app logs (nginx + php-fpm + entrypoint)
- `docker compose exec app sh` — shell into the app container
- `docker compose exec db psql -U portfolio portfolio` — psql into the dev database
- `docker build -f docker/php/Dockerfile --target prod -t portfolio:prod .` — build the production image locally

## Symfony console

- `docker compose exec app bin/console cache:clear` — clear the app cache
- `docker compose exec app bin/console debug:router` — list all routes
- `docker compose exec app bin/console debug:container <id>` — inspect a service
- `docker compose exec app bin/console app:seed` — seed initial content (no-op if content exists)

## Doctrine

- `docker compose exec app bin/console doctrine:migrations:diff` — generate a migration from entity changes
- `docker compose exec app bin/console doctrine:migrations:migrate -n` — run pending migrations
- `docker compose exec app bin/console doctrine:migrations:migrate prev -n` — roll back one migration
- `docker compose exec app bin/console doctrine:schema:validate` — validate mapping vs. schema
- `docker compose exec app bin/console dbal:run-sql "SELECT * FROM profile"` — run raw SQL

## Testing

- `docker compose exec app bin/console doctrine:database:create --env=test --if-not-exists` — create the test DB (once)
- `docker compose exec app bin/console doctrine:migrations:migrate --env=test -n` — migrate the test DB
- `docker compose exec app vendor/bin/phpunit --testsuite unit` — unit tests only
- `docker compose exec app vendor/bin/phpunit --testsuite integration` — integration tests only
- `docker compose exec app vendor/bin/phpunit` — unit + integration
- `docker compose exec app vendor/bin/phpunit -c phpunit.e2e.xml` — E2E tests (Chromium inside the container)
- `docker compose exec app vendor/bin/phpunit --filter testName` — run a single test

## Composer

- `docker compose exec app composer require <pkg>` — add a dependency
- `docker compose exec app composer require --dev <pkg>` — add a dev dependency
- `docker compose exec app composer validate` — validate composer.json/lock

## CI (reproduce locally)

- `docker compose exec app sh -c 'vendor/bin/phpunit --testsuite unit && vendor/bin/phpunit --testsuite integration && vendor/bin/phpunit -c phpunit.e2e.xml'` — full pipeline
- CI itself: `.github/workflows/ci.yaml` (PHP 8.4 + Postgres 18 service + Chrome)
- Image publish: `.github/workflows/docker-publish.yaml` — pushes `ghcr.io/<repo>:latest` + `:sha-…` for `linux/amd64,linux/arm64` on every push to `main`/`master`

## Production container

- Required env vars: `DATABASE_URL`, `ADMIN_USERNAME`, `ADMIN_PASSWORD`, `APP_SECRET` (see `.env.example`)
- `docker run --rm -e APP_ENV=prod -e ADMIN_PASSWORD=admin ... portfolio:prod` — exits immediately: default password is rejected
- Entrypoint order: wait for DB → migrations → seed-if-empty → cache warmup → nginx + php-fpm
