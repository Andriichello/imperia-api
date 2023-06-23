FROM ubuntu:focal

ARG ENV
ARG AWS_ACCESS_KEY_ID
ARG AWS_SECRET_ACCESS_KEY

# Set up environment for installing packages
RUN export LANG=C.UTF-8 TZ=Etc/UTC && apt-get update

# Install Packages
RUN export LANG=C.UTF-8 \
    && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install --no-install-recommends --yes software-properties-common apt-transport-https \
    && apt-get --yes install gnupg software-properties-common tzdata \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update \
    && apt-get install --yes --allow-unauthenticated --no-install-recommends \
        nginx supervisor \
        wget git unzip curl nano dos2unix \
        php8.1 php8.1-cli php8.1-fpm \
        php8.1-intl php8.1-xml php8.1-zip php8.1-curl \
        php8.1-http php8.1-raphf \
        php8.1-mbstring php8.1-memcached php8.1-mysql \
        openssh-client \
        python3-pip \
    && pip3 install -U setuptools \
    && pip3 install awscli \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Apply the filesystem overlay, which mainly provides scripts in /opt.
COPY resources/docker /

# Next, we copy the entire project.
COPY . /var/www/imperia-api

# We begin by creating a workspace where the project will recide.
WORKDIR /var/www/imperia-api

# Set up permissions for the project.
RUN chown -R www-data:www-data ./storage

RUN aws configure set aws_access_key_id ${AWS_ACCESS_KEY_ID} \
    && aws configure set aws_secret_access_key ${AWS_SECRET_ACCESS_KEY} \
    && aws s3 --endpoint-url https://storage.googleapis.com cp s3://imperia-api-secrets/auth.json auth.json \
    && aws s3 --endpoint-url https://storage.googleapis.com cp s3://imperia-api-secrets/.env.${ENV} .env

# Install and setup Apache, Composer and Nginx.
RUN chmod -R u+x ./resources/scripts \
    ./resources/scripts/setup-dirs.sh \
    ./resources/scripts/setup-logs.sh

# Install Composer dependencies.
RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php \
    && php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && composer install -o -n

# Expose HTTP and HTTPS ports.
EXPOSE 80 443

# The default script for the image will mainly start PHP and Apache.
CMD ["/opt/start.sh"]

