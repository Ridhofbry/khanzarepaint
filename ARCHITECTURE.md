# Khanza Repaint - System Architecture

## 🏗️ Project Structure

```
khanzarepaint/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Application controllers
│   │   ├── Middleware/         # Request middleware (auth, roles)
│   │   └── Requests/          # Form request validation
│   ├── Livewire/
│   │   └── Components/         # Interactive UI components
│   ├── Models/                # Eloquent models
│   ├── Services/              # Business logic services
│   ├── Policies/              # Authorization policies
│   ├── Observers/             # Model observers for events
│   └── Providers/             # Service providers
├── database/
│   ├── migrations/            # Database schema
│   ├── factories/             # Model factories for testing
│   └── seeders/              # Database seeders
├── resources/
│   ├── views/
│   │   ├── layouts/          # Main layout templates
│   │   ├── pages/            # Page views
│   │   ├── components/       # Reusable components
│   │   └── livewire/         # Livewire component views
│   ├── css/
│   │   └── app.css          # Tailwind + custom styles
│   └── js/
│       └── app.js           # JavaScript entry point
├── routes/
│   ├── web.php              # Web routes
│   └── api.php              # API routes (future)
├── config/
│   ├── database.php         # Database configuration
│   └── cloudinary.php       # Cloudinary configuration
├── tests/
│   ├── Unit/                # Unit tests
│   └── Feature/             # Feature tests
├── .env.example             # Environment template
├── composer.json            # PHP dependencies
├── package.json             # Node dependencies
├── vite.config.js          # Vite configuration
├── tailwind.config.js      # Tailwind configuration
├── README.md               # Main documentation
├── SETUP.md                # Setup instructions
└── API_DOCS.md             # API documentation
```

## 🔄 Data Flow Architecture

### Booking Flow (with Double Prevention)

```
User Request
    ↓
BookingController::store
    ↓
Request Validation
    ↓
BookingService::createBooking
    ↓
DB::transaction() {
    ├─ User::lockForUpdate()
    ├─ Booking::lockForUpdate() → Check existing booking
    ├─ Voucher validation (if applicable)
    ├─ Calculate discount
    ├─ Create Booking record
    ├─ Update user service_count
    ├─ Update membership tier
    └─ Record activity
}
    ↓
Return result or rollback on error
```

### Voucher Claim Flow

```
User Request
    ↓
VoucherController::store
    ↓
Check for duplicate claims
    ↓
Validate voucher (active, not expired)
    ↓
Create VoucherClaim record
    ↓
Return success response
```

### Car Search Flow

```
Search Request with filters
    ↓
GarageController::index
    ↓
Car::where('status', 'available')
    ├─ if search → search() scope
    ├─ if price range → priceRange() scope
    ├─ if fuel type → fuelType() scope
    └─ if year → year() scope
    ↓
Eager load seller relationship
    ↓
Paginate results (12 per page)
    ↓
Return view with cars
```

## 🗄️ Database Relationships

```
users (1) ──────────────── (N) bookings
 │                          │
 │                          └── (N) vouchers (used)
 │                          
 ├──────────────────── (N) cars (as seller)
 │                          
 └──────────────────── (N) testimonials
                            │
                            └── (1) bookings
                            
services (1) ─────────── (N) bookings
                          │
                          └── vouchers (applicable_to)

vouchers (1) ────────── (N) voucher_claims
                            │
                            └── (1) users
                            └── (1) bookings

faqs (no relationships - standalone reference data)
```

## 🔐 Authentication & Authorization Flow

```
User Login
    ↓
Laravel Breeze/Fortify
    ├─ Validate credentials
    ├─ Check email verification
    └─ Create session/token
    ↓
User authenticated (stored in Auth::user())
    ↓
Route Middleware check
    ├─ auth → Authenticated user required
    ├─ role:admin → User role must be 'admin'
    ├─ role:garage_owner → User role must be 'garage_owner'
    └─ verified → Email must be verified
    ↓
Policy authorization (view/update/delete)
    ├─ BookingPolicy::view → User owns booking or is admin
    ├─ CarPolicy::update → User is seller or is admin
    └─ Custom gates for special operations
```

## 📊 Membership Tier Logic

```
Service Count → Tier Upgrade
0-1 services → None (0% discount)
2-3 services → Bronze (5% discount)
4-6 services → Silver (10% discount)
7+ services  → Gold (15% discount)

Automatic Trigger:
When user.service_count increases via User::recordService()
```

## 🎯 Cloudinary Integration Points

```
User uploads image
    ↓
CloudinaryService::uploadImage()
    ├─ Validate file size (max 5MB)
    ├─ Validate format (jpg, png, webp, gif)
    ├─ Upload to Cloudinary
    │  ├─ Convert to WebP
    │  ├─ Auto quality detection
    │  ├─ Progressive rendering
    │  └─ Responsive width handling
    ├─ Store URL in database
    └─ Return optimized URL
    
Retrieved URL on frontend
    ↓
Serve from Cloudinary CDN
    ├─ Auto format detection
    ├─ DPR-based delivery
    └─ 1-year cache TTL
```

## 🔍 Query Optimization Strategy

### N+1 Prevention

```php
// ❌ BAD: N+1 queries
$bookings = Booking::all(); // 1 query
foreach ($bookings as $booking) {
    echo $booking->service->name; // N queries
}

// ✅ GOOD: Eager loading
$bookings = Booking::with('service')->get(); // 1 query
foreach ($bookings as $booking) {
    echo $booking->service->name; // 0 additional queries
}
```

### Database Indexes

All frequently queried columns are indexed:

```
users table:
  - email (unique)
  - role
  - membership_tier

services table:
  - type
  - is_active

bookings table:
  - user_id
  - service_id
  - status
  - scheduled_date
  - unique(user_id, scheduled_date)

cars table:
  - seller_id
  - brand
  - year
  - price
  - status
  - fuel_type

vouchers table:
  - code (unique)
  - is_active
  - expires_at
```

## 🛡️ Error Handling & Logging

```
Exception Occurs
    ↓
    ├─ Log to logs/laravel.log (error level)
    │  └─ Include context: user_id, request data, error message
    │
    ├─ If user-facing error:
    │  └─ Return friendly error message (no technical details)
    │
    └─ If admin/debug mode:
       └─ Show detailed error page with stack trace
```

## 📱 Frontend Architecture (Livewire)

```
Navigation Component
    ├─ isAuthenticated (boolean)
    ├─ user (User model)
    ├─ membershipTier (string)
    ├─ toggleMobileMenu() method
    └─ logout() method
        ↓
    Renders navigation.blade.php
    ├─ Shows different menu based on auth state
    ├─ Displays user profile menu
    └─ Mobile responsive hamburger menu

Interactive Components (Livewire)
    ├─ Booking form with time slot selection
    ├─ Voucher claim button
    ├─ Car gallery with filters
    └─ Real-time status updates
```

## 🚀 Performance Metrics

- **Database queries per page**: <5 (with eager loading)
- **Image size**: <100KB (via Cloudinary WebP)
- **Page load time**: <2s (with caching)
- **Time to first byte**: <500ms
- **Database connections**: Connection pooling via Turso

## 🔄 CI/CD Pipeline (Recommended)

```
Git Push
    ↓
1. Run tests (PHPUnit)
2. Run linting (Pint)
3. Run security checks
4. Build frontend assets
5. Run database migrations (staging)
6. Deploy to production
    └─ If all pass: Deploy
    └─ If any fail: Rollback and notify
```

## 📈 Scalability Considerations

1. **Database**: Turso allows read replicas for geographic distribution
2. **Storage**: Cloudinary handles unlimited file storage with CDN
3. **Sessions**: Database sessions can be switched to Redis
4. **Caching**: Implement Redis caching layer for frequently accessed data
5. **Queue**: Add job queue for async operations (email, image processing)
6. **Load Balancing**: Multiple PHP-FPM processes behind load balancer
