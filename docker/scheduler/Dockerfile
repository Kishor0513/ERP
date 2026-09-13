FROM php:8.3-fpm-alpine

# Install system dependencies including cron
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
    dcron

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    bcmath \
    gd \
    pcntl \
    intl \
    opcache

# Install Redis extension
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create user for running commands
RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www

# Create required directories
RUN mkdir -p /var/www/html/storage \
    && chown -R www:www /var/www/html \
    && chmod -R 775 /var/www/html/storage

# Copy scheduler entrypoint
COPY docker/scheduler/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set up cron
RUN crontab -l | { cat; echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1"; } | crontab -

# Switch to non-root user
USER www:www

ENTRYPOINT ["entrypoint.sh"]
