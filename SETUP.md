# Khanza Repaint - Quick Start Guide

## 🚀 Quick Installation

### Step 1: Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Configure your environment variables:
# - Set DATABASE_URL for Turso/LibSQL
# - Set Cloudinary credentials
# - Set email configuration
```

### Step 2: Install Dependencies

```bash
# PHP dependencies
composer install

# Node dependencies
npm install
```

### Step 3: Setup Application

```bash
# Generate app key
php artisan key:generate

# Create database
php artisan migrate

# Seed sample data (optional)
php artisan db:seed
```

### Step 4: Build Frontend Assets

```bash
# Development build
npm run dev

# Production build
npm run build
```

### Step 5: Start Development Server

```bash
# Terminal 1: Start PHP server
php artisan serve

# Terminal 2: Start Vite dev server
npm run dev
```

Visit `http://localhost:8000` in your browser.

## 🔧 Configuration Details

### Turso/LibSQL Setup

1. **Create Turso Account**
   - Go to https://turso.tech
   - Create a new database
   - Note your connection URL and auth token

2. **Update .env**
   ```env
   TURSO_CONNECTION_URL=libsql://your-db-name-username.turso.io
   TURSO_AUTH_TOKEN=your_auth_token_here
   ```

### Cloudinary Setup

1. **Create Cloudinary Account**
   - Go to https://cloudinary.com
   - Sign up and get API credentials

2. **Update .env**
   ```env
   CLOUDINARY_CLOUD_NAME=your_cloud_name
   CLOUDINARY_API_KEY=your_api_key
   CLOUDINARY_API_SECRET=your_api_secret
   ```

3. **Create Upload Presets** (in Cloudinary dashboard)
   - `khanza_cars` - For car images
   - `khanza_services` - For service images
   - `khanza_profiles` - For profile images

## 📱 Creating Test Users

### Via Artisan Command

```bash
# Create an admin user
php artisan tinker
>>> User::create([
    'name' => 'Admin User',
    'email' => 'admin@khanzarepaint.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
]);

# Create a garage owner
>>> User::create([
    'name' => 'Garage Owner',
    'email' => 'garage@khanzarepaint.com',
    'password' => bcrypt('password'),
    'role' => 'garage_owner',
]);

# Create a customer
>>> User::create([
    'name' => 'John Doe',
    'email' => 'customer@khanzarepaint.com',
    'password' => bcrypt('password'),
    'role' => 'customer',
]);
```

## 🧪 Testing the Application

### Create a Test Booking

```bash
php artisan tinker

# Get a user and service
$user = User::where('role', 'customer')->first();
$service = Service::first();

# Create booking
$booking = Booking::create([
    'user_id' => $user->id,
    'service_id' => $service->id,
    'scheduled_date' => now()->addDays(3),
    'status' => 'pending',
    'total_price' => $service->price,
    'booking_code' => Booking::generateBookingCode(),
]);
```

### Test Voucher Claim

```php
# Create a voucher
$voucher = Voucher::create([
    'code' => Voucher::generateCode(),
    'description' => 'Test Voucher',
    'discount_amount' => 100000, // 100k cents = Rp 1,000
    'is_active' => true,
    'expires_at' => now()->addMonth(),
    'starts_at' => now(),
]);

# Claim voucher
$claim = VoucherClaim::create([
    'voucher_id' => $voucher->id,
    'user_id' => $user->id,
    'claimed_at' => now(),
    'status' => 'pending',
]);
```

## 🔑 Key Features to Test

### 1. Double Booking Prevention

```php
# Try to create two bookings at same time
$booking1 = BookingService::createBooking($user, $service, '2024-12-25 10:00');
$booking2 = BookingService::createBooking($user, $service, '2024-12-25 10:00');
// Second one will fail due to atomic transaction
```

### 2. Membership Tier Upgrade

```php
# Check current tier
$user->membership_tier; // 'none'

# Record 2 services
$user->recordService(500000);
$user->recordService(500000);

# Check tier - should be 'bronze'
$user->fresh()->membership_tier; // 'bronze'
$user->getMembershipDiscount(); // 5%
```

### 3. Car Search with Filters

```php
# Search by brand
$cars = Car::search('Toyota')->get();

# Search by price range (in cents)
$cars = Car::priceRange(100000000, 500000000)->get();

# Search by year
$cars = Car::year(2023)->get();

# Combine filters
$cars = Car::search('Honda')
    ->priceRange(100000000, 300000000)
    ->fuelType('petrol')
    ->get();
```

### 4. Voucher Validation

```php
# Create an expired voucher
$expiredVoucher = Voucher::create([
    'code' => Voucher::generateCode(),
    'description' => 'Expired',
    'discount_amount' => 50000,
    'is_active' => true,
    'expires_at' => now()->subDay(), // Already expired
    'starts_at' => now()->subMonth(),
]);

# Try to use it
$expiredVoucher->canBeUsed(); // false
```

## 📊 Database Queries

### View All Bookings with Details

```bash
php artisan tinker
>>> Booking::with('user', 'service', 'voucher')->get()
```

### Get Users by Membership Tier

```bash
>>> User::membershipTier('gold')->count()
>>> User::membershipTier('silver')->get()
```

### Find Available Cars

```bash
>>> Car::available()->search('BMW')->priceRange(200000000, 500000000)->get()
```

### Get Active Vouchers

```bash
>>> Voucher::valid()->orderBy('expires_at')->get()
```

## 🚨 Troubleshooting

### Issue: Database connection fails

**Solution:**
```bash
# Check .env configuration
php artisan tinker
>>> config('database.default')
>>> DB::connection()->getPdo()
```

### Issue: Cloudinary upload fails

**Solution:**
```bash
# Test Cloudinary connection
php artisan tinker
>>> $service = app(CloudinaryService::class)
>>> $service->validateConnection() // Should return true
```

### Issue: Assets not loading

**Solution:**
```bash
# Rebuild Vite assets
npm run build

# Or run in development mode
npm run dev
```

### Issue: Livewire components not working

**Solution:**
```bash
# Ensure Livewire is properly installed
composer require livewire/livewire

# Clear cache
php artisan cache:clear
php artisan view:clear
```

## 📝 Important Notes

1. **Database**: Always backup before running migrations on production
2. **Cloudinary**: Test API credentials before going live
3. **Email**: Configure SMTP for production email notifications
4. **Security**: Change default passwords and app key in production
5. **HTTPS**: Always use HTTPS in production

## 🆘 Getting Help

For issues or questions:
1. Check the main [README.md](README.md)
2. Review Laravel documentation: https://laravel.com/docs
3. Check Livewire docs: https://livewire.laravel.com
4. Visit Cloudinary docs: https://cloudinary.com/documentation
