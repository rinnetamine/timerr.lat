#!/usr/bin/env sh
set -eu

# Docker konteiners visas Laravel komandas izpilda projekta saknē.
cd /var/www/html

# Ja vide vēl nav sagatavota, tā tiek izveidota no piemēra faila.
if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

# Atjaunina vai pievieno vienu .env vērtību, saglabājot failu vienkāršā KEY=value formātā.
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

# Docker Compose vides mainīgie tiek sinhronizēti ar Laravel .env failu.
for key in APP_ENV APP_DEBUG APP_KEY APP_URL DB_CONNECTION DB_DATABASE SESSION_DRIVER CACHE_STORE QUEUE_CONNECTION; do
    eval "value=\${$key:-}"
    if [ -n "$value" ]; then
        set_env_value "$key" "$value"
    fi
done

db_file="${DB_DATABASE:-/data/database.sqlite}"
db_dir="$(dirname "$db_file")"

# SQLite datubāzei un Laravel kešatmiņas mapēm jāeksistē pirms migrāciju palaišanas.
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

# Ja APP_KEY nav padota no Compose konfigurācijas, Laravel to izveido pats.
if [ -f .env ] && [ -z "${APP_KEY:-}" ] && ! grep -Eq '^APP_KEY=.+$' .env; then
    php artisan key:generate --force --no-interaction
fi

# Palaišanas laikā tiek sakārtoti pakotņu servisi, public storage saite un datubāzes migrācijas.
php artisan package:discover --ansi
php artisan storage:link --force
php artisan migrate --force

exec "$@"
