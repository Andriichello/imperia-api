#!/bin/bash

cd /var/www/imperia-api

# Create .env if it doesn't exist
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

service php8.0-fpm start
service apache2 start
service mysql start

composer install -o -n

php artisan key:generate
php artisan config:cache
php artisan route:cache

php artisan migrate
php artisan l5-swagger:generate
php artisan queue:work
