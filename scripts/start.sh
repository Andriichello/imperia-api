#!/usr/bin/env bash
set -e

composer update

php artisan migrate
php artisan serve
