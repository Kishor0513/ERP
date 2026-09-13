#!/bin/sh
set -e

# Wait for Laravel application to be ready
echo "Waiting for Laravel application to be ready..."
sleep 15

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

# Start cron daemon in background
echo "Starting cron daemon..."
crond -f -l 2 &

# Keep container running
echo "Scheduler is running..."
exec tail -f /dev/null
