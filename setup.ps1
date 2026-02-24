#Requires -Version 5.0

<#
.SYNOPSIS
    Khanza Repaint - Automated Laravel 11 Setup Script
.DESCRIPTION
    This script automates the setup of Khanza Repaint application
    - Verifies PHP installation
    - Downloads and installs Composer dependencies
    - Sets up environment variables
    - Runs database migrations
#>

Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║     KHANZA REPAINT - AUTOMATED SETUP SCRIPT               ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Refresh PATH
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")

# Step 1: Verify PHP
Write-Host "Step 1: Verifying PHP Installation..." -ForegroundColor Yellow
$phpVersion = php -v 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ PHP is installed:" -ForegroundColor Green
    Write-Host $phpVersion.Split("`n")[0] -ForegroundColor Green
} else {
    Write-Host "❌ PHP is not installed or not in PATH" -ForegroundColor Red
    Write-Host "Please install PHP 8.3+ and add to PATH" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Step 2: Download Composer if needed
Write-Host "Step 2: Preparing Composer..." -ForegroundColor Yellow
if (-Not (Test-Path "composer.phar")) {
    Write-Host "Downloading Composer (this may take a minute)..."
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    try {
        Invoke-WebRequest -Uri "https://getcomposer.org/composer.phar" -OutFile "composer.phar" -UseBasicParsing -ErrorAction Stop
        Write-Host "✅ Composer downloaded successfully" -ForegroundColor Green
    } catch {
        Write-Host "❌ Failed to download Composer: $_" -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "✅ Composer already exists" -ForegroundColor Green
}

Write-Host ""

# Step 3: Install dependencies
Write-Host "Step 3: Installing Laravel Dependencies..." -ForegroundColor Yellow
Write-Host "This may take 2-5 minutes. Please wait..." -ForegroundColor Cyan
php composer.phar install --no-interaction

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Composer install failed" -ForegroundColor Red
    exit 1
} else {
    Write-Host "✅ Laravel dependencies installed" -ForegroundColor Green
}

Write-Host ""

# Step 4: Check .env
Write-Host "Step 4: Setting Up Environment Variables..." -ForegroundColor Yellow
if (-Not (Test-Path ".env")) {
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env"
        Write-Host "✅ .env file created from .env.example" -ForegroundColor Green
        Write-Host "⚠️  Edit .env with your Turso & Cloudinary credentials" -ForegroundColor Yellow
    }
} else {
    Write-Host "✅ .env file already exists" -ForegroundColor Green
}

Write-Host ""

# Step 5: Generate app key
Write-Host "Step 5: Generating Application Key..." -ForegroundColor Yellow
php artisan key:generate --no-interaction

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Application key generated" -ForegroundColor Green
} else {
    Write-Host "⚠️  Could not generate app key (may already exist)" -ForegroundColor Yellow
}

Write-Host ""

# Step 6: Run migrations
Write-Host "Step 6: Running Database Migrations..." -ForegroundColor Yellow
Write-Host "⚠️  Make sure your .env has correct database configuration" -ForegroundColor Yellow
php artisan migrate --force --no-interaction

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Database migrations completed" -ForegroundColor Green
} else {
    Write-Host "⚠️  Migrations may have failed - check your database configuration" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║              ✅ SETUP COMPLETE!                           ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""

Write-Host "📋 NEXT STEPS:" -ForegroundColor Cyan
Write-Host ""
Write-Host "1️⃣  Start the development server:"
Write-Host "   php artisan serve" -ForegroundColor Yellow
Write-Host ""
Write-Host "2️⃣  Visit in your browser:"
Write-Host "   http://localhost:8000" -ForegroundColor Yellow
Write-Host ""
Write-Host "3️⃣  Install Laravel Breeze for authentication (optional):"
Write-Host "   php artisan breeze:install" -ForegroundColor Yellow
Write-Host ""
Write-Host "For more information, see START_HERE.md" -ForegroundColor Cyan
Write-Host ""
