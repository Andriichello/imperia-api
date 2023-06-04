#!/bin/bash
set -euo pipefail

cd /var/www/imperia-api

composer install -o -n

php artisan key:generate
php artisan config:cache
php artisan route:cache

php artisan migrate
php artisan l5-swagger:generate

nohup php artisan queue:work --daemon &

# Create /var/run/php if it doesn't exist
if [ ! -d "/var/run/php" ]; then
    mkdir -p /var/run/php
fi

# Start supervisord, which manages Nginx and PHP-FPM for us.
exec supervisord -c /etc/supervisor/supervisord.conf
