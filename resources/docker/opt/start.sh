#!/bin/bash
set -euo pipefail

cd /var/www/imperia-api

cat .env

php artisan storage:link
php artisan cache:clear
php artisan route:cache
php artisan l5-swagger:generate

# Create /var/run/php if it doesn't exist
if [ ! -d "/var/run/php" ]; then
    mkdir -p /var/run/php
fi

echo "starting php8.1-fpm";
service php8.1-fpm start

echo "starting nginx";
nginx
