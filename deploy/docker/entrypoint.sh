#!/bin/sh
# Container entrypoint shared by the app, queue, scheduler and reverb services.
#   - app generates APP_KEY / REVERB_APP_SECRET once and keeps them on the shared storage volume
#   - app waits for the database, migrates, and seeds only on the very first run
#   - everything else waits for the app to publish the secrets and finish migrating
set -e

cd /var/www/html

SECRETS=storage/docker-secrets.env
READY=storage/.docker-ready

if [ "$CONTAINER_ROLE" = "app" ]; then
    if [ ! -f "$SECRETS" ]; then
        APP_KEY_VALUE="base64:$(head -c 32 /dev/urandom | base64)"
        REVERB_SECRET_VALUE="$(head -c 16 /dev/urandom | od -An -tx1 | tr -d ' \n')"
        {
            echo "APP_KEY=$APP_KEY_VALUE"
            echo "REVERB_APP_SECRET=$REVERB_SECRET_VALUE"
        } > "$SECRETS"
    fi
    rm -f "$READY"
else
    until [ -f "$READY" ]; do
        echo "Waiting for the app container to finish setting up..."
        sleep 2
    done
fi

set -a
. "./$SECRETS"
set +a

# Also written as .env so `docker compose exec app php artisan ...` (which skips this script) sees them.
cp "./$SECRETS" .env

if [ "$CONTAINER_ROLE" = "app" ]; then
    echo "Waiting for the database..."
    until php artisan migrate:status >/dev/null 2>&1 || php -r '
        try { new PDO("mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); exit(0); }
        catch (Throwable $e) { exit(1); }'; do
        sleep 2
    done

    FIRST_RUN=0
    if ! php artisan migrate:status >/dev/null 2>&1; then
        FIRST_RUN=1
    fi

    php artisan migrate --force

    if [ "$FIRST_RUN" = "1" ]; then
        echo "First run: seeding the admin, viewer and demo machine..."
        php artisan db:seed --force
    fi

    touch "$READY"
fi

exec "$@"
