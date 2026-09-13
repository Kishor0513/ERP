#!/bin/sh
set -e

# Wait for Laravel application to be ready
echo "Waiting for Laravel application to be ready..."
sleep 10

# Install dependencies if needed
if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Clear and rebuild cache
echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start queue worker
echo "Starting queue worker..."
exec php artisan queue:work redis \
    --sleep=3 \
    --tries=3 \
    --max-time=3600 \
    --max-jobs=1000 \
    --memory=512
