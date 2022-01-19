#!/usr/bin/env bash
set -e

FILE=./.env
if [ ! -f "$FILE" ]; then
    cp .env.example .env
    php artisan key:generate
fi

composer install

php artisan migrate:fresh --seed
