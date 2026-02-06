#!/bin/bash

# Laravel Security PoC Setup Script
echo "--- Starting Laravel Security PoC Setup ---"

# 1. Check for PHP
if ! command -v php &> /dev/null; then
    echo "Error: PHP is not installed. Please install PHP 8.2 or higher."
    exit 1
fi

# 2. Check for Composer
if ! command -v composer &> /dev/null; then
    echo "Error: Composer is not installed. Please install it from getcomposer.org."
    exit 1
fi

# 3. Create Required Directory Structure
echo "Initializing directory structure..."
mkdir -p bootstrap/cache
mkdir -p storage/app/public
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/testing
mkdir -p storage/framework/views
mkdir -p storage/logs

# 4. Set Permissions
if [[ "$OSTYPE" != "msys" && "$OSTYPE" != "win32" ]]; then
    echo "Setting permissions..."
    chmod -R 775 storage bootstrap/cache
fi

# 5. Environment Setup
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cp .env.example .env
fi

# 6. Install Dependencies
echo "Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# 7. Application Key
echo "Generating app key..."
php artisan key:generate

# 8. Database Initialization
echo "Setting up SQLite database..."
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate --force

# 9. Clear Caches
echo "Clearing application caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 10. Finalize
echo "--- Setup Complete! ---"
echo "Starting local development server..."
php artisan serve