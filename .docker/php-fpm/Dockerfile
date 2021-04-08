FROM php:7.4.16-fpm

RUN apt-get update && apt-get install -y \
    apt-utils \
    gnupg \
    lsb-release \
    git \
    g++ \
    wget \
    ssh \
    unzip \
    libbz2-dev \
    libgmp-dev \
    libicu-dev \
    libmemcached-dev \
    libzip-dev \
    libpq-dev \
    zip

RUN wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add - \
    && echo "deb http://apt.postgresql.org/pub/repos/apt/ `lsb_release -cs`-pgdg main" |tee  /etc/apt/sources.list.d/pgdg.list

RUN apt-get install -y \
    libcurl4-gnutls-dev \
    libfreetype6-dev \
    libjpeg-dev \
    libjpeg62-turbo-dev \
    libpcre++-dev \
    libpng-dev \
    libxpm-dev \
    libxml2-dev \
    zlib1g-dev

# Base PHP Extensions
RUN docker-php-ext-install -j$(nproc) bcmath \
    && docker-php-ext-install -j$(nproc) bz2 \
    && docker-php-ext-install -j$(nproc) calendar \
    && docker-php-ext-install -j$(nproc) exif \
    && docker-php-ext-install -j$(nproc) gmp \
    && docker-php-ext-install -j$(nproc) intl \
    && docker-php-ext-install -j$(nproc) opcache \
    && docker-php-ext-install -j$(nproc) soap \
    && docker-php-ext-install -j$(nproc) sockets \
    && docker-php-ext-install -j$(nproc) zip

RUN pecl install apcu && docker-php-ext-enable apcu
#RUN pecl install dbase && docker-php-ext-enable dbase
RUN pecl install memcached && docker-php-ext-enable memcached
#RUN pecl install redis && docker-php-ext-enable redis
RUN pecl install timezonedb && docker-php-ext-enable timezonedb

### MariaDB
#RUN apt-get install -y mariadb-client \
#    && docker-php-ext-install -j$(nproc) mysqli \
#    && docker-php-ext-install -j$(nproc) pdo_mysql

### PostgreSQL
RUN echo "installing postgresql 13.2 or higher..."
RUN apt-get update --fix-missing && apt-get install -y postgresql-client \
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

# Cleanups
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

### Configs
COPY ./apcu.ini /usr/local/etc/php/conf.d/apcu.ini
COPY ./global.ini /usr/local/etc/php/conf.d/zz-global.ini
#COPY ./opcache.ini /usr/local/etc/php/conf.d/opcache.ini
#COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
#COPY ./fpm.www.conf /usr/local/etc/php-fpm.d/www.conf
