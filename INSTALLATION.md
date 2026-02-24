# ⚙️ INSTALLATION GUIDE - KHANZA REPAINT

## ✅ Current Status

- **PHP 8.3.30** ✅ Installed and working
- **Composer** ✅ Downloaded (composer.phar available)
- **Laravel** 🔄 Installing (background process)
- **Database** ⏳ Ready for setup
- **Node.js** 📝 Optional (for CSS/JS building)

---

## 📋 INSTALLATION OPTIONS

### **Option 1: Quick Setup (PowerShell - RECOMMENDED)**

```powershell
# Open PowerShell in this directory and run:

# Refresh PATH
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")

# Install dependencies (first time only)
php composer.phar install

# Setup environment
cp .env.example .env

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate

# Start server
php artisan serve

# Visit http://localhost:8000
```

---

### **Option 2: Automated Script (PowerShell)**

```powershell
# Run the setup script (in PowerShell)
Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope Process
.\setup.ps1

# This handles all steps automatically
```

---

### **Option 3: Batch File (CMD)**

```cmd
# Run in Windows Command Prompt
setup.bat

# Simpler alternative if PowerShell scripts don't work
```

---

## 🔧 STEP-BY-STEP INSTALLATION

### Step 1: Verify Prerequisites ✅

```powershell
# Check PHP
php -v
# Expected: PHP 8.3.30 (cli)

# Check Composer
php composer.phar --version
# Expected: Composer version 2.x
```

**Status**: ✅ Both working!

---

### Step 2: Install Laravel Dependencies

```powershell
# This downloads all Laravel packages (~300+ packages)
php composer.phar install

# Time: 2-5 minutes depending on internet speed
# Creates: vendor/ directory (~300MB)
```

**What gets installed**:
- Laravel 11 framework
- Livewire 3 for interactivity
- Tailwind CSS for styling
- Database drivers
- Testing frameworks
- And 300+ more packages

---

### Step 3: Configure Environment

```powershell
# Copy example configuration
cp .env.example .env

# Edit .env with your settings:
# - APP_NAME=Khanza Repaint
# - DB_CONNECTION=turso (or sqlite)
# - CLOUDINARY_CLOUD_NAME=your_cloud_name
# - etc.
```

**Key variables to update**:
```
TURSO_CONNECTION_URL=your_turso_url
TURSO_AUTH_TOKEN=your_turso_token
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
```

---

### Step 4: Generate Application Key

```powershell
# This creates encryption key for Laravel
php artisan key:generate
```

---

### Step 5: Run Database Migrations

```powershell
# Creates all 8 database tables
php artisan migrate

# You'll see output like:
# Migration: 2024_01_01_000000_create_users_table
# Migration: 2024_01_02_000000_create_services_table
# ...
```

---

### Step 6: Start the Application

```powershell
# Terminal 1: Start Laravel development server
php artisan serve
# Server runs on http://localhost:8000

# Terminal 2: (Optional) Build frontend assets
npm run dev
# Watch and compile CSS/JS (if Node.js installed)
```

---

## 🌐 VERIFICATION

After startup, visit: **http://localhost:8000**

You should see:
- ✅ Professional landing page with red-black-white theme
- ✅ Navigation bar with Services, Booking, Garage, etc.
- ✅ Feature showcase
- ✅ Testimonials section
- ✅ Call-to-action buttons

---

## 🐛 TROUBLESHOOTING

### Problem: "PHP not recognized"

**Solution**:
```powershell
# Refresh PATH in PowerShell
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
php -v
```

---

### Problem: "Composer not found"

**Solution**: It's downloaded as `composer.phar` in project root
```powershell
# Use full path
php composer.phar install
```

---

### Problem: "composer.phar download failed"

**Solution**: Download manually
```powershell
php -r "copy('https://getcomposer.org/composer.phar', 'composer.phar');"
```

---

### Problem: "Database connection error"

**Solution**:
1. Check `.env` has correct `DB_` variables
2. For Turso: Set `DB_CONNECTION=turso`
3. For SQLite: Use `DB_CONNECTION=sqlite` (database.sqlite auto-created)
4. For MySQL: Install MySQL locally and configure

---

### Problem: "SQLSTATE[HY000] database file cannot be opened"

**Solution**:
```powershell
# Create database file
touch database/database.sqlite
# Or Windows equivalent
New-Item -Path database\database.sqlite -ItemType File

# Then run migrations
php artisan migrate
```

---

### Problem: "npm not found" (non-critical)

**Solution**: Optional for now
- CSS/JS already compiled and included
- Run `php artisan serve` without npm
- For development, install Node.js later

---

## 📱 NEXT STEPS AFTER INSTALLATION

### 1. Install Authentication (Laravel Breeze)

```powershell
php artisan breeze:install
# Select: Livewire stack
npm install && npm run build
php artisan migrate
```

### 2. Create Test Users

```powershell
php artisan tinker

>>> $user = User::factory()->create(['role' => 'customer']);
>>> $user->email
# Result: you have a test user

>>> Auth::loginUsingId($user->id);
>>> exit
```

### 3. Test Key Features

- [ ] Visit landing page (http://localhost:8000)
- [ ] Create a service via Tinker
- [ ] Create a booking (test double-booking prevention)
- [ ] Apply voucher (test duplicate prevention)
- [ ] Search cars with filters (test null-safe search)
- [ ] Upload image via form (test Cloudinary)

### 4. Deploy to Production

See SETUP.md → Production Deployment section

---

## 🎓 USEFUL COMMANDS

```powershell
# Refresh PATH (do this in each PowerShell window)
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")

# Start development server
php artisan serve

# Run database migrations
php artisan migrate

# Create test data
php artisan db:seed

# Open Laravel Tinker (interactive shell)
php artisan tinker

# Build frontend assets (if npm installed)
npm run dev    # Watch mode
npm run build  # Production

# View logs
php artisan logs tail

# Clear cache
php artisan cache:clear
php artisan config:cache
```

---

## 📞 SUPPORT

- **Documentation**: See [DOCS_INDEX.md](DOCS_INDEX.md)
- **API Reference**: See [API_DOCS.md](API_DOCS.md)
- **Architecture**: See [ARCHITECTURE.md](ARCHITECTURE.md)
- **Setup Issues**: Check Troubleshooting section above
- **Laravel Docs**: https://laravel.com/docs/11

---

## ✅ INSTALLATION CHECKLIST

- [ ] PHP 8.3+ installed and working
- [ ] Composer downloaded (composer.phar exists)
- [ ] Dependencies installed (vendor/ directory exists)
- [ ] .env configured with database credentials
- [ ] App key generated (APP_KEY set)
- [ ] Database migrations ran (tables created)
- [ ] Server starts successfully
- [ ] Landing page loads at http://localhost:8000
- [ ] Features working (navigation, forms, etc.)

---

**Version**: 1.0
**Last Updated**: February 25, 2026
**Status**: ✅ Ready to use
