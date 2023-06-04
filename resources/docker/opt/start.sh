#!/bin/bash
set -euo pipefail

cd /var/www/imperia-api

composer config --auth http-basic.nova.laravel.com ${NOVA_USERNAME} ${NOVA_PASSWORD}
composer install -o -n

php artisan storage:link
php artisan cache:clear
php artisan config:cache
php artisan route:cache

php artisan l5-swagger:generate

php artisan migrate

# Create /var/run/php if it doesn't exist
if [ ! -d "/var/run/php" ]; then
    mkdir -p /var/run/php
fi

echo "starting php8.1-fpm";
service php8.1-fpm start

echo "starting nginx";
nginx
