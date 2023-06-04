# Create log files and directories.
mkdir -p /var/log/supervisor
> /var/log/supervisor/supervisord.log

mkdir -p /var/log/nginx
> /var/log/nginx/access.log
> /var/log/nginx/error.log

mkdir -p /var/log/php-fpm
> /var/log/php-fpm/stdout.log
> /var/log/php-fpm/stderr.log

chmod -R 770 /var/log
