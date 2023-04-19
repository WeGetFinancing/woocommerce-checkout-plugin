FROM php:8.0-fpm

#install magento's required extensions
RUN apt-get -y update \
    && apt-get -y install \
        libzip-dev \
        zip \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libicu-dev \
        libxml2-dev \
        libxslt-dev \
    && apt-get -y clean

RUN docker-php-ext-install zip

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN docker-php-ext-configure intl \
    && docker-php-ext-install intl

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN docker-php-ext-install soap

RUN docker-php-ext-install xsl

RUN docker-php-ext-install sockets

# Install composer
COPY --from=composer/composer:2 /usr/bin/composer /usr/bin/composer
RUN chmod +x /usr/bin/composer

WORKDIR /app

ENTRYPOINT ["composer"]
