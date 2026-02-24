# Install PHP and Composer for Khanza Repaint
# Run as Administrator

param(
    [string]$PhpVersion = "8.3.14"
)

Write-Host "================================================" -ForegroundColor Cyan
Write-Host "  Installing PHP $PhpVersion & Composer" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan

# Check if running as Administrator
if (-not ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Host "⚠ Please run as Administrator!" -ForegroundColor Red
    exit
}

# Step 1: Create PHP directory
$phpDir = "C:\php"
if (-not (Test-Path $phpDir)) {
    Write-Host "`n[1/5] Creating PHP directory..." -ForegroundColor Yellow
    New-Item -ItemType Directory -Path $phpDir -Force | Out-Null
    Write-Host "✓ Created: $phpDir" -ForegroundColor Green
} else {
    Write-Host "`n[1/5] PHP directory already exists" -ForegroundColor Green
}

# Step 2: Download PHP
Write-Host "`n[2/5] Downloading PHP $PhpVersion..." -ForegroundColor Yellow
$phpUrl = "https://windows.php.net/downloads/releases/php-$PhpVersion-nts-Win32-x64.zip"
$phpZip = "$phpDir\php.zip"

try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    $ProgressPreference = 'SilentlyContinue'
    Invoke-WebRequest -Uri $phpUrl -OutFile $phpZip -UseBasicParsing
    Write-Host "✓ Downloaded PHP" -ForegroundColor Green
} catch {
    Write-Host "✗ Download failed: $_" -ForegroundColor Red
    Write-Host "Try downloading manually from: $phpUrl" -ForegroundColor Yellow
    exit 1
}

# Step 3: Extract PHP
Write-Host "`n[3/5] Extracting PHP..." -ForegroundColor Yellow
try {
    Expand-Archive -Path $phpZip -DestinationPath $phpDir -Force
    Remove-Item $phpZip -Force
    Write-Host "✓ Extracted PHP" -ForegroundColor Green
} catch {
    Write-Host "✗ Extraction failed: $_" -ForegroundColor Red
    exit 1
}

# Step 4: Add PHP to System PATH
Write-Host "`n[4/5] Adding PHP to System PATH..." -ForegroundColor Yellow
$currentPath = [Environment]::GetEnvironmentVariable("PATH", "Machine")
if ($currentPath -notlike "*$phpDir*") {
    [Environment]::SetEnvironmentVariable("PATH", "$phpDir;$currentPath", "Machine")
    Write-Host "✓ Added PHP to System PATH" -ForegroundColor Green
} else {
    Write-Host "✓ PHP already in PATH" -ForegroundColor Green
}

# Step 5: Verify PHP
Write-Host "`n[5/5] Verifying installation..." -ForegroundColor Yellow

# Refresh PATH for current session
$env:PATH = "$phpDir;$env:PATH"

try {
    $phpVersion = & "$phpDir\php.exe" --version 2>&1 | Select-Object -First 1
    Write-Host "✓ PHP ready: $phpVersion" -ForegroundColor Green
} catch {
    Write-Host "⚠ Could not verify PHP (might need to restart terminal)" -ForegroundColor Yellow
}

# Step 6: Setup Composer
Write-Host "`n[6/6] Setting up Composer..." -ForegroundColor Yellow

# Check if composer.phar exists in project
$composerPhar = "C:\Users\$env:USERNAME\Documents\khanzarepaint\composer.phar"
if (Test-Path $composerPhar) {
    Write-Host "✓ composer.phar found in project" -ForegroundColor Green
    
    # Create batch file for easy composer access
    $composerBat = "C:\php\composer.bat"
    @"
@echo off
php "$composerPhar" %*
"@ | Out-File -FilePath $composerBat -Encoding ASCII -Force
    Write-Host "✓ Created composer.bat for easy access" -ForegroundColor Green
} else {
    Write-Host "ℹ composer.phar not found in project" -ForegroundColor Yellow
}

Write-Host "`n================================================" -ForegroundColor Cyan
Write-Host "  Installation Complete!" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "`nNext Steps:" -ForegroundColor Cyan
Write-Host "1. Close and reopen PowerShell to refresh PATH" -ForegroundColor White
Write-Host "2. Navigate to: C:\Users\$env:USERNAME\Documents\khanzarepaint" -ForegroundColor White
Write-Host "3. Run: php --version" -ForegroundColor White
Write-Host "4. Run: php composer.phar --version" -ForegroundColor White
Write-Host "5. Follow FILAMENT_SETUP.md for next steps" -ForegroundColor White
Write-Host "`n"
