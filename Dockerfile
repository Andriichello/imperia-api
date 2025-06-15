FROM ubuntu:jammy

ARG ENV
ARG AWS_ACCESS_KEY_ID
ARG AWS_SECRET_ACCESS_KEY

# Set up environment for installing packages
RUN export LANG=C.UTF-8 TZ=Etc/UTC && apt-get update

# Install Packages
RUN export LANG=C.UTF-8 \
    && export DEBIAN_FRONTEND=noninteractive \
    && sed -i 's/http:\/\/archive.ubuntu.com\/ubuntu\//http:\/\/mirrors.edge.kernel.org\/ubuntu\//g' /etc/apt/sources.list \
    && apt-get update \
    && apt-get install --no-install-recommends --yes software-properties-common apt-transport-https \
    && apt-get --yes install gnupg software-properties-common tzdata \
    && add-apt-repository ppa:ondrej/php \
    && apt-get update \
    && apt-get install --yes --allow-unauthenticated --no-install-recommends \
        nginx supervisor \
        wget git unzip curl nano dos2unix \
        php8.3 php8.3-cli php8.3-fpm \
        php8.3-intl php8.3-xml php8.3-zip php8.3-curl \
        php8.3-http php8.3-raphf php8.3-gd \
        php8.3-mbstring php8.3-memcached php8.3-mysql \
        mysql-client \
        openssh-client \
        python3-pip \
    && pip3 install -U setuptools \
    && pip3 install awscli \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install nvm and setup npm
RUN mkdir /usr/local/nvm
ENV NVM_DIR /usr/local/nvm
ENV NODE_VERSION 22.14.0
RUN curl https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.1/install.sh | bash \
    && . $NVM_DIR/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && nvm use default

ENV NODE_PATH $NVM_DIR/v$NODE_VERSION/lib/node_modules
ENV PATH $NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH

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
    && aws s3 --endpoint-url https://storage.googleapis.com cp s3://imperia-api-secrets/google-cloud.json google-cloud.json \
    && aws s3 --endpoint-url https://storage.googleapis.com cp s3://imperia-api-secrets/.env.${ENV} .env

# Install and setup Apache, Composer and Nginx.
RUN chmod -R u+x ./resources/scripts \
    ./resources/scripts/setup-dirs.sh \
    ./resources/scripts/setup-logs.sh

# Install Composer dependencies.
RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php \
    && php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && composer install -o -n --no-dev

RUN npm ci --audit false \
    && npm run prod \
    && php artisan vendor:publish --force --tag=livewire:assets

# Expose HTTP and HTTPS ports.
EXPOSE 80 443

# The default script for the image will mainly start PHP and Apache.
CMD ["/opt/start.sh"]

