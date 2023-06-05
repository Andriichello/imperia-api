#!/bin/bash
set -euo pipefail

cd /var/www/imperia-api

echo "setting up S3 credentials";
aws configure set aws_access_key_id ${AWS_ACCESS_KEY_ID}
aws configure set aws_secret_access_key ${AWS_SECRET_ACCESS_KEY}

echo "loading .env from S3";
aws s3 --endpoint-url https://storage.googleapis.com cp s3://imperia-api-secrets/.env.staging .env

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
