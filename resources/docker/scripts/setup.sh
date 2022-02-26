#!/bin/bash

# Create databases for development and testing
mysql -e "
    create database if not exists imperia;
    create database if not exists imperia_test;
    update mysql.user set Password = PASSWORD('password') where User = 'root';
    drop user ''@'localhost';
    drop user ''@'$(hostname)';
    drop database test;
    flush privileges;
    "

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
php artisan storage:link

php artisan migrate:fresh --seed --force
