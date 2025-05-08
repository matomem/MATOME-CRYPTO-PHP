#!/bin/bash

# Exit on error
set -e

# Configuration
APP_DIR="/var/www/matomecrypto"
BACKUP_DIR="/var/backups/matomecrypto"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Create backup
echo "Creating backup..."
mkdir -p $BACKUP_DIR
tar -czf $BACKUP_DIR/backup_$TIMESTAMP.tar.gz $APP_DIR

# Pull latest changes
echo "Pulling latest changes..."
cd $APP_DIR
git pull origin main

# Install dependencies
echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Run database migrations
echo "Running database migrations..."
php migrate.php migrate

# Clear cache
echo "Clearing cache..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Set permissions
echo "Setting permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 777 $APP_DIR/storage

# Restart services
echo "Restarting services..."
systemctl restart php-fpm
systemctl restart nginx

# Verify deployment
echo "Verifying deployment..."
curl -s http://localhost/health > /dev/null

echo "Deployment completed successfully!" 