# Create .env if it doesn't exist
if [ ! -f ".env" ]; then
    cp .env.heroku .env
fi

php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan storage:link

php artisan migrate:fresh --seed --force
