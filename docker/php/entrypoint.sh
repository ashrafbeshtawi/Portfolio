#!/bin/sh
set -e

# Refuse to boot a production container with missing or default admin credentials.
if [ "$APP_ENV" = "prod" ]; then
    if [ -z "$ADMIN_USERNAME" ] || [ -z "$ADMIN_PASSWORD" ] || [ "$ADMIN_PASSWORD" = "admin" ]; then
        echo "ERROR: ADMIN_USERNAME and a non-default ADMIN_PASSWORD must be provided as environment variables in production." >&2
        exit 1
    fi
    if [ -z "$APP_SECRET" ]; then
        echo "WARNING: APP_SECRET is not set — sessions and CSRF tokens will use the insecure default." >&2
    fi
fi

# Dev convenience: install dependencies on first boot of a fresh clone.
if [ "$APP_ENV" != "prod" ] && [ ! -f vendor/autoload_runtime.php ]; then
    echo "vendor/ missing — running composer install..."
    composer install --no-interaction --no-progress
fi

if [ -n "$DATABASE_URL" ]; then
    echo "Waiting for the database..."
    tries=0
    until php bin/console dbal:run-sql "SELECT 1" >/dev/null 2>&1; do
        tries=$((tries + 1))
        if [ "$tries" -ge 60 ]; then
            echo "ERROR: database is not reachable after 60s." >&2
            exit 1
        fi
        sleep 1
    done

    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
    php bin/console app:seed
fi

# The entrypoint runs as root; php-fpm workers run as www-data and must be
# able to write the cache, logs and sessions.
if [ "$APP_ENV" = "prod" ]; then
    php bin/console cache:warmup
    chown -R www-data:www-data var
fi

exec "$@"
