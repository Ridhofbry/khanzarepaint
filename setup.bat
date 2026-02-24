@echo off
REM Khanza Repaint - Simple Setup Batch Script
REM For Windows CMD (not PowerShell)

cls
echo.
echo ============================================================
echo     KHANZA REPAINT - SETUP SCRIPT
echo ============================================================
echo.

REM Check PHP
echo Checking PHP installation...
php -v >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP not found in PATH
    echo Please install PHP 8.3+ or add it to your PATH
    pause
    exit /b 1
)
echo OK: PHP found

REM Download Composer if needed
echo.
echo Preparing Composer...
if not exist composer.phar (
    echo Downloading Composer...
    php -r "copy('https://getcomposer.org/composer.phar', 'composer.phar');"
    if errorlevel 1 (
        echo ERROR: Failed to download Composer
        pause
        exit /b 1
    )
)
echo OK: Composer ready

REM Install dependencies
echo.
echo Installing Laravel dependencies...
echo This may take 2-5 minutes...
php composer.phar install --no-interaction
if errorlevel 1 (
    echo ERROR: Composer install failed
    pause
    exit /b 1
)
echo OK: Dependencies installed

REM Setup .env
echo.
echo Setting up environment...
if not exist .env (
    if exist .env.example (
        copy .env.example .env
        echo OK: .env created
        echo WARNING: Edit .env with your database credentials
    )
)

REM Generate key
echo.
echo Generating application key...
php artisan key:generate --no-interaction

REM Run migrations
echo.
echo Running migrations...
php artisan migrate --force --no-interaction
if errorlevel 1 (
    echo WARNING: Migrations may have failed
    echo Check your database configuration in .env
)

echo.
echo ============================================================
echo     SETUP COMPLETE!
echo ============================================================
echo.
echo NEXT STEPS:
echo 1. Start server: php artisan serve
echo 2. Visit: http://localhost:8000
echo.
pause
