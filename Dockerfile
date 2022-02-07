FROM ubuntu:20.04

# Set up environment for installing packages
RUN export LANG=C.UTF-8 TZ=Etc/UTC && apt-get update

# Install Packages
RUN apt-get install --no-install-recommends --yes software-properties-common apt-transport-https \
    && apt-get --yes install tzdata \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install --yes --allow-unauthenticated --no-install-recommends \
        wget git unzip curl nano \
        php8.0 php8.0-cli php8.0-fpm \
        php8.0-intl php8.0-xml php8.0-zip php8.0-curl \
        php8.0-http php8.0-raphf \
        php8.0-mbstring php8.0-memcached php8.0-mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Download and install Composer
RUN cd ~  \
    && curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php \
    && php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Download and install MySQL
RUN apt-get update && apt-get install mysql-server --yes

# Download and install MySQL
RUN apt-get update && apt-get install apache2 --yes

COPY . /var/www/imperia-api
WORKDIR /var/www/imperia-api

# Set permissions for folders
RUN chown -R www-data:www-data /var/www/imperia-api/public \
    && chown -R www-data:www-data /var/www/imperia-api/bootstrap/cache/ \
    && chown -R www-data:www-data /var/www/imperia-api/storage \
    && chmod -R u+x /var/www/imperia-api/resources/docker/scripts

# Set up MySQL
RUN service mysql start \
    && mysql -e " \
      create database if not exists imperia; \
      create database if not exists imperia_test; \
      alter user 'root'@'localhost' identified with mysql_native_password BY 'password'; \
      flush privileges; \
    "

# Set up Apache
RUN cp -f /var/www/imperia-api/resources/docker/configs/apache2.conf /etc/apache2/apache2.conf \
    && cp -f /var/www/imperia-api/resources/docker/configs/ports.conf /etc/apache2/ports.conf \
    && cp -f /var/www/imperia-api/resources/docker/configs/imperia-api.conf /etc/apache2/sites-available/imperia-api.conf \
    && service apache2 start \
    && a2dissite 000-default.conf \
    && a2ensite imperia-api.conf \
    && a2enmod proxy_fcgi setenvif rewrite ssl \
    && a2enconf php8.0-fpm \
    && service apache2 reload

# Replace file endings
RUN sed -i 's/\r$//' /var/www/imperia-api/resources/docker/scripts/setup.sh
# Set up application
RUN /var/www/imperia-api/resources/docker/scripts/setup.sh

# Expose ports
EXPOSE 80

# Replace file endings
RUN sed -i 's/\r$//' /var/www/imperia-api/resources/docker/scripts/start.sh
# Start up application when container starts
ENTRYPOINT /var/www/imperia-api/resources/docker/scripts/start.sh
