FROM ubuntu:jammy

# Set up environment for installing packages
RUN export LANG=C.UTF-8 TZ=Etc/UTC && apt-get update

# Install Packages
RUN apt-get install --no-install-recommends --yes software-properties-common apt-transport-https \
    && apt-get --yes install tzdata \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install --yes --allow-unauthenticated --no-install-recommends \
        wget git unzip curl nano dos2unix \
        php8.1 php8.1-cli php8.1-fpm \
        php8.1-intl php8.1-xml php8.1-zip php8.1-curl \
        php8.1-http php8.1-raphf \
        php8.1-mbstring php8.1-memcached php8.1-mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Download and install Composer
RUN cd ~  \
    && curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php \
    && php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Download and install MySQL
RUN apt-get update && apt-get install mysql-server --yes

# Download and install Apache
RUN apt-get update && apt-get install apache2 --yes

# Set up MySQL
RUN service mysql start \
    && mysql -e " \
      create database if not exists imperia; \
      create database if not exists imperia_test; \
      alter user 'root'@'localhost' identified with mysql_native_password BY 'password'; \
      flush privileges; \
    "

# Set up Apache config
COPY ./resources/docker/configs/apache2.conf /etc/apache2/apache2.conf
COPY ./resources/docker/configs/ports.conf /etc/apache2/ports.conf
COPY ./resources/docker/configs/imperia-api.conf /etc/apache2/sites-available/imperia-api.conf

# Set up Apache service
RUN service apache2 start \
    && a2dissite 000-default.conf \
    && a2ensite imperia-api.conf \
    && a2enmod proxy_fcgi setenvif rewrite ssl \
    && a2enconf php8.1-fpm \
    && service apache2 reload

# Set up scripts
COPY ./resources/docker/scripts /var/www/imperia-api/resources/docker/scripts
RUN find //var/www/imperia-api/resources/docker/scripts/ -type f -print0 | xargs -0 dos2unix -- \
    && chmod -R u+x /var/www/imperia-api/resources/docker/scripts

EXPOSE 80 443
