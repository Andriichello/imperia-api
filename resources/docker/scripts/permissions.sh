#!/bin/bash

# Set permissions for folders
chown -R www-data:www-data /var/www/imperia-api/public
chown -R www-data:www-data /var/www/imperia-api/bootstrap/cache
chown -R www-data:www-data /var/www/imperia-api/storage
chmod -R u+x /var/www/imperia-api/resources/docker/scripts
