#!/bin/bash
set -euo pipefail

cd /var/www/imperia-api

echo 'username: '
echo ${NOVA_USERNAME}
echo '   password: '
echo ${NOVA_PASSWORD}

composer config http-basic.nova.laravel.com ${NOVA_USERNAME} ${NOVA_PASSWORD}
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
