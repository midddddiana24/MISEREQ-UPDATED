@echo off
REM Deploy script for MISEREQ - Run this during deployment/server restart
REM This script ensures Laravel is ready and queue workers are restarted

setlocal enabledelayedexpansion

cd c:\xampp_new\htdocs\mis-service-request\mis-service-request

echo.
echo ======================================
echo MISEREQ Deploy Script
echo ======================================
echo.

REM 1. Ensure .env file exists
if not exist .env (
    echo ERROR: .env file not found. Please copy .env.example to .env and configure it.
    exit /b 1
)

REM 2. Clear all caches
echo [1/5] Clearing application caches...
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

REM 3. Run pending migrations
echo [2/5] Running database migrations...
php artisan migrate --force

REM 4. Seed database if needed (optional - uncomment if needed)
REM echo [3/5] Seeding database...
REM php artisan db:seed --force

echo [3/5] Generating application cache...
php artisan config:cache
php artisan route:cache
php artisan view:cache

REM 5. Queue restart - critical for workers to pick up new code
echo [4/5] Restarting queue workers...
php artisan queue:restart

REM 6. Show queue status
echo [5/5] Queue status:
php artisan queue:failed

echo.
echo ======================================
echo Deploy complete!
echo ======================================
echo.
echo Next steps:
echo 1. Start queue worker supervisor:
echo    powershell -ExecutionPolicy Bypass -File scripts\queue-worker-supervisor.ps1
echo.
echo 2. Monitor queue jobs:
echo    php artisan queue:work database --sleep=3 --tries=3 --timeout=90
echo.
