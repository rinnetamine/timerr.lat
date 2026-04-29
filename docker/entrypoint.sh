#!/usr/bin/env sh
set -eu

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

set_env_value() {
    key="$1"
    value="$2"
    escaped_value="$(printf '%s' "$value" | sed 's/[\/&]/\\&/g')"

    if grep -q "^${key}=" .env; then
        sed -i "s/^${key}=.*/${key}=${escaped_value}/" .env
    else
        printf '\n%s=%s\n' "$key" "$value" >> .env
    fi
}

for key in APP_ENV APP_DEBUG APP_KEY APP_URL DB_CONNECTION DB_DATABASE SESSION_DRIVER CACHE_STORE QUEUE_CONNECTION; do
    eval "value=\${$key:-}"
    if [ -n "$value" ]; then
        set_env_value "$key" "$value"
    fi
done

db_file="${DB_DATABASE:-/data/database.sqlite}"
db_dir="$(dirname "$db_file")"

mkdir -p \
    "$db_dir" \
    bootstrap/cache \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

touch "$db_file"
chmod -R ug+rwX "$db_dir" bootstrap/cache storage
rm -f bootstrap/cache/*.php

if [ -f .env ] && [ -z "${APP_KEY:-}" ] && ! grep -Eq '^APP_KEY=.+$' .env; then
    php artisan key:generate --force --no-interaction
fi

php artisan package:discover --ansi
php artisan storage:link --force
php artisan migrate --force

exec "$@"
