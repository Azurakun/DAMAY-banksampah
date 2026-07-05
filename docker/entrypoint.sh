#!/bin/sh
set -e

# Path to SQLite database file
DB_FILE="/var/www/html/database/database.sqlite"

# Create the SQLite database file if it does not exist
if [ ! -f "$DB_FILE" ]; then
    echo "SQLite database file not found. Creating $DB_FILE..."
    mkdir -p "/var/www/html/database"
    touch "$DB_FILE"
fi

# Ensure storage directories exist
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs

# Fix permissions so that both nginx/php-fpm (running as www-data) can read/write
echo "Setting permissions for storage and database..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/database
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/database
if [ -f "$DB_FILE" ]; then
    chmod 664 "$DB_FILE"
fi

# Run laravel optimization and database migration commands
echo "Running Laravel deployment commands..."
php artisan storage:link --force || true
php artisan migrate --force

echo "Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Execute supervisor to launch Nginx, PHP-FPM, Queue worker, and Reverb
echo "Starting supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
