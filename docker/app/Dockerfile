FROM php:8.3-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    icu-dev \
    libzip-dev \
    autoconf \
    gcc \
    g++ \
    make \
    libpq-dev \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    bcmath \
    gd \
    pcntl \
    exif \
    intl \
    opcache \
    xml

# Install Redis extension
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Swoole (optional, for high-performance)
# RUN pecl install swoole \
#     && docker-php-ext-enable swoole

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Node.js (LTS)
RUN apk add --no-cache nodejs npm

# Configure PHP
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copy custom PHP configuration
COPY docker/app/php.ini /usr/local/etc/php/conf.d/custom.ini

# Set working directory
WORKDIR /var/www/html

# Create user for running commands
RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www

# Create required directories
RUN mkdir -p /var/www/html/storage \
    && chown -R www:www /var/www/html \
    && chmod -R 775 /var/www/html/storage

# Switch to non-root user
USER www:www

EXPOSE 9000
CMD ["php-fpm"]
