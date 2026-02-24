# Khanza Repaint - Professional Automotive Web Application

A modern, production-ready Laravel 11 application for automotive repaint and general services with an integrated marketplace platform.

## 📋 Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [System Architecture](#system-architecture)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Migrations](#database-migrations)
- [API Documentation](#api-documentation)
- [Security](#security)
- [Performance Optimization](#performance-optimization)

## ✨ Features

### Core Modules

1. **Landing Page**
   - Professional Red-Black-White theme
   - High-converting hero section
   - Feature showcase
   - Testimonial preview
   - Call-to-action sections

2. **Service Management**
   - Repaint and general services
   - Dynamic pricing
   - Service duration tracking
   - Included features listing

3. **Real-Time Booking System**
   - Atomic transactions prevent double booking
   - Available time slot management
   - Booking status tracking (pending, confirmed, in_progress, completed, cancelled)
   - Unique booking codes

4. **Garage Marketplace**
   - Car listing with Cloudinary image integration
   - Advanced search and filtering
   - Price range filtering
   - Fuel type and year filtering
   - View counter
   - Car seller profiles

5. **Membership System**
   - **Bronze Tier**: 2-3 services (5% discount)
   - **Silver Tier**: 4-6 services (10% discount)
   - **Gold Tier**: 7+ services (15% discount)
   - Automatic tier upgrades based on transaction frequency

6. **Voucher Engine**
   - Dynamic unique code generation
   - Fixed and percentage discounts
   - Expiry logic with date validation
   - Per-user usage limits
   - Duplicate claim prevention
   - Minimum purchase requirements
   - Service-specific vouchers

7. **Testimonials & FAQ**
   - Customer testimonials with ratings
   - Admin approval system
   - Featured testimonials
   - Searchable FAQ with categories

## 🛠 Tech Stack

### Backend
- **Framework**: Laravel 11 (PHP 8.3)
- **Database**: Turso/LibSQL (SQLite with cloud sync)
- **Authentication**: Laravel Breeze/Fortify
- **ORM**: Eloquent with eager loading

### Frontend
- **CSS**: Tailwind CSS 3.3
- **Interactive Components**: Livewire 3
- **Templating**: Blade

### Storage & Media
- **Cloud Storage**: Cloudinary
- **Image Optimization**: WebP conversion, auto quality detection
- **CDN**: Cloudinary CDN with 1-year cache

### Development
- **Package Manager**: Composer
- **Frontend Build**: Vite
- **Testing**: PHPUnit (unit & feature tests)

## 🏗 System Architecture

### Database Schema

```
users
├── Basic authentication fields
├── Phone and address
├── Role-based (customer, admin, garage_owner)
├── Membership tier tracking
├── Service count and total spent
└── Last login tracking

services
├── Name and description
├── Type (repaint, general)
├── Price and duration
├── Image URL (Cloudinary)
├── Active status
└── Included features

bookings
├── User and service relationship
├── Scheduled datetime
├── Status tracking
├── Unique booking code
├── Voucher and discount tracking
├── Atomic transaction support
└── Cancellation details

cars
├── Seller relationship
├── Brand, model, year, color
├── License plate (unique)
├── Price and mileage
├── Fuel type and transmission
├── Multiple images (JSON array)
├── Features list (JSON)
├── Status and view counter
└── Full-text search support

vouchers
├── Unique code generation
├── Fixed or percentage discounts
├── Usage limits and tracking
├── Date range validation
├── Applicable service types
├── Minimum purchase requirements
└── Duplicate claim prevention

testimonials
├── User and booking relationship
├── Rating (1-5 stars)
├── Featured and approval status
└── Image support

faqs
├── Question and answer
├── Category grouping
├── Active status
└── Sort order
```

### Transaction Handling

Double booking prevention uses row-level locking:

```php
DB::transaction(function () {
    $lockedUser = User::lockForUpdate()->find($user->id);
    $existingBooking = Booking::lockForUpdate()
        ->where('user_id', $user->id)
        ->where('scheduled_date', $scheduledDate)
        ->first();
    // ... handle booking creation
}, attempts: 3);
```

### Eager Loading Optimization

All queries use eager loading to avoid N+1 queries:

```php
// ❌ Bad: N+1 queries
$bookings = Booking::all();
foreach ($bookings as $booking) {
    echo $booking->service->name; // Query per iteration
}

// ✅ Good: Single query
$bookings = Booking::with('service', 'user', 'voucher')->get();
```

## 📦 Installation

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 16+
- npm or yarn

### Steps

```bash
# Clone the repository
git clone https://github.com/yourusername/khanzarepaint.git
cd khanzarepaint

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Set up database
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Build frontend assets
npm run build

# Start development server
php artisan serve
```

## ⚙️ Configuration

### Environment Variables

```env
# App
APP_NAME="Khanza Repaint"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database - Turso/LibSQL
DB_CONNECTION=sqlite
DB_DATABASE=database.db
TURSO_CONNECTION_URL=libsql://your-database.turso.io
TURSO_AUTH_TOKEN=your_auth_token_here

# Cloudinary
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret

# Mail
MAIL_FROM_ADDRESS=admin@khanzarepaint.com
MAIL_FROM_NAME="Khanza Repaint"

# Session & Cache
SESSION_DRIVER=database
CACHE_DRIVER=database
```

### Cloudinary Setup

1. Create a Cloudinary account at [cloudinary.com](https://cloudinary.com)
2. Get your Cloud Name, API Key, and API Secret
3. Set environment variables
4. Create upload presets for different file types

### Turso Database Setup

1. Sign up at [turso.tech](https://turso.tech)
2. Create a new database
3. Get connection URL and auth token
4. Update `.env` file

## 🗄 Database Migrations

Run all migrations:

```bash
php artisan migrate
```

Create new migration:

```bash
php artisan make:migration create_table_name
```

Rollback migrations:

```bash
php artisan migrate:rollback
```

## 🔒 Security

### Implemented Security Measures

1. **Input Validation & Sanitization**
   - All inputs validated using Laravel validation rules
   - Blade templating auto-escapes output (XSS prevention)
   - Eloquent ORM prevents SQL injection

2. **Role-Based Access Control (RBAC)**
   ```php
   Route::middleware('role:admin')->group(function () {
       // Admin routes
   });
   ```

3. **Double Booking Prevention**
   - Atomic database transactions with row-level locking
   - Unique constraint on user + scheduled_date

4. **Duplicate Voucher Claim Prevention**
   - Unique constraint on voucher_id + user_id
   - Usage-per-user tracking
   - Claim status validation

5. **API Security**
   - CSRF token protection on all forms
   - Rate limiting (via middleware)
   - Sanctum authentication for API endpoints

6. **File Upload Security**
   - File size validation
   - Format validation (whitelist: jpg, png, webp, gif)
   - Cloudinary handles malware scanning

## ⚡ Performance Optimization

### Eager Loading

```php
// Load relationships to prevent N+1 queries
$bookings = Booking::with('service', 'user', 'voucher')
    ->paginate(15);
```

### Image Optimization

Cloudinary automatically optimizes images:

```php
// WebP conversion
format: 'webp'

// Auto quality detection
quality: 'auto'

// Responsive images
responsive_width: true

// Progressive loading
flags: 'progressive'
```

### Indexing Strategy

All frequently queried columns have indexes:

```php
// Booking table
$table->index('user_id');
$table->index('status');
$table->index('scheduled_date');
$table->unique(['user_id', 'scheduled_date']); // Double booking prevention
```

### Caching Recommendations

```php
// Cache expensive queries
$services = Cache::remember('services.active', 3600, function () {
    return Service::where('is_active', true)->get();
});
```

## 🧪 Testing

Run all tests:

```bash
php artisan test
```

Run specific test:

```bash
php artisan test tests/Feature/BookingTest.php
```

## 📝 Error Handling & Root Cause Analysis

### Booking Failure Example

```php
// If booking fails, detailed logs are created:
Log::error('Booking creation failed', [
    'error' => $e->getMessage(),
    'user_id' => $user->id,
    'service_id' => $service->id,
]);

// User-friendly response:
return [
    'success' => false,
    'message' => 'User already has a booking at this time',
];
```

### Edge Cases Handled

1. **Null Inputs on Garage Search**
   ```php
   if (!$keyword) {
       return $query;
   }
   ```

2. **API Timeouts for Cloudinary**
   - Try-catch blocks with fallback URLs
   - Queued image processing
   - Retry logic with exponential backoff

3. **Voucher Expiry**
   ```php
   ->where('expires_at', '>=', now())
   ->where('starts_at', '<=', now())
   ```

## 🚀 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Set up HTTPS/SSL
- [ ] Configure CDN for static assets
- [ ] Set up proper database backups
- [ ] Configure email service
- [ ] Set up logging and monitoring

## 📞 Support & Contribution

For issues, feature requests, or contributions, please open an issue or submit a pull request.

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

**Built with ❤️ for automotive enthusiasts**
