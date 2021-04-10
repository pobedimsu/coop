FROM php:7.4.16-cli

RUN apt-get update && apt-get install -qq -y \
    apt-utils \
    bash-completion \
    git \
    gnupg \
    nano \
    lsb-release \
    g++ \
    wget \
    ssh \
    unzip \
    libbz2-dev \
    libgmp-dev \
    libevent-dev \
    libicu-dev \
    libmemcached-dev \
    libzip-dev \
    libpq-dev \
    libssl-dev \
    openssl \
    zip

RUN wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add - \
    && echo "deb http://apt.postgresql.org/pub/repos/apt/ `lsb_release -cs`-pgdg main" |tee  /etc/apt/sources.list.d/pgdg.list

RUN apt-get install -qq -y \
    libcurl4-gnutls-dev \
    libfreetype6-dev \
    libjpeg-dev \
    libjpeg62-turbo-dev \
    libpcre++-dev \
    libpng-dev \
    libxpm-dev \
    libxml2-dev \
    zlib1g-dev

### Base PHP Extensions
RUN docker-php-ext-install -j$(nproc) bcmath \
    && docker-php-ext-install -j$(nproc) bz2 \
    && docker-php-ext-install -j$(nproc) calendar \
    && docker-php-ext-install -j$(nproc) exif \
    && docker-php-ext-install -j$(nproc) gmp \
    && docker-php-ext-install -j$(nproc) intl \
    && docker-php-ext-install -j$(nproc) opcache \
#    && docker-php-ext-install -j$(nproc) pcntl \
#    && docker-php-ext-install -j$(nproc) soap \
#    && docker-php-ext-install -j$(nproc) sockets \
    && docker-php-ext-install -j$(nproc) zip

RUN pecl install apcu && docker-php-ext-enable apcu
#RUN pecl install dbase && docker-php-ext-enable dbase
#RUN pecl install event && docker-php-ext-enable event
RUN pecl install memcached && docker-php-ext-enable memcached
#RUN pecl install redis && docker-php-ext-enable redis
RUN pecl install timezonedb && docker-php-ext-enable timezonedb

### MariaDB
#RUN apt-get install -y mariadb-client \
#    && docker-php-ext-install -j$(nproc) mysqli \
#    && docker-php-ext-install -j$(nproc) pdo_mysql

### PostgreSQL
RUN echo "installing postgresql 13.2 or higher..."
RUN apt-get update --fix-missing && apt-get install -qq -y postgresql-client \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install -j$(nproc) pgsql \
    && docker-php-ext-install -j$(nproc) pdo_pgsql

### GD
RUN docker-php-ext-configure gd \
    --with-freetype=/usr/include/ \
    --with-jpeg=/usr/include/ \
    --with-xpm=/usr/lib/x86_64-linux-gnu \
    && docker-php-ext-install -j$(nproc) gd

### ImageMagick
#RUN apt-get install -y imagemagick libmagickcore-dev libmagickwand-dev \
#    && pecl install imagick \
#    && docker-php-ext-enable imagick

RUN wget -O /usr/local/bin/dumb-init https://github.com/Yelp/dumb-init/releases/download/v1.2.5/dumb-init_1.2.5_x86_64
RUN chmod +x /usr/local/bin/dumb-init

### Custom soft
#RUN apt-get install -y mc htop cron rsyslog

### Cleanups
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

### Configs
COPY ./.bashrc /root/.bashrc
COPY ./symfony-console-autocomplete /etc/bash_completion.d/symfony-console-autocomplete
COPY ./apcu.ini /usr/local/etc/php/conf.d/apcu.ini
COPY ./global.ini /usr/local/etc/php/conf.d/zz-global.ini

### Composer
RUN echo "installing Composer v2.0.11 or higher..."
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME '/root/.composer'
