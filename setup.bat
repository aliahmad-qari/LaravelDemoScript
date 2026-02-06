@echo off
echo --- Starting Laravel Security PoC Setup (Windows) ---

:: 1. Check for PHP
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo Error: PHP is not installed or not in your PATH.
    pause
    exit /b 1
)

:: 2. Check for Composer
composer -v >nul 2>&1
if %errorlevel% neq 0 (
    echo Error: Composer is not installed or not in your PATH.
    pause
    exit /b 1
)

:: 3. Create Required Directory Structure
echo Initializing directory structure...
if not exist "bootstrap\cache" mkdir "bootstrap\cache"
if not exist "storage\app\public" mkdir "storage\app\public"
if not exist "storage\framework\cache\data" mkdir "storage\framework\cache\data"
if not exist "storage\framework\sessions" mkdir "storage\framework\sessions"
if not exist "storage\framework\testing" mkdir "storage\framework\testing"
if not exist "storage\framework\views" mkdir "storage\framework\views"
if not exist "storage\logs" mkdir "storage\logs"

:: 4. Environment Setup
if not exist ".env" (
    echo Creating .env file from .env.example...
    copy .env.example .env
)

:: 5. Install Dependencies
echo Installing Composer dependencies...
call composer install --no-interaction --prefer-dist --optimize-autoloader

:: Verification: check if vendor was created
if not exist "vendor" (
    echo FATAL ERROR: Composer failed to create the vendor directory. 
    echo Please run 'composer install' manually to see the error.
    pause
    exit /b 1
)

:: 6. Application Key
echo Generating app key...
call php artisan key:generate

:: 7. Database Initialization
echo Setting up SQLite database...
if exist "database\database.sqlite" del "database\database.sqlite"
type nul > database\database.sqlite
call php artisan migrate --force

:: 8. Final Cache Clear
echo Clearing application caches...
call php artisan config:clear
call php artisan cache:clear
call php artisan view:clear

echo --- Setup Complete! ---
echo Starting local development server...
call php artisan serve