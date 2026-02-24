# 🎛️ FILAMENT ADMIN BACKEND SETUP GUIDE

## 📋 OVERVIEW

This guide covers the installation and setup of **FilamentPHP v3 Admin Backend** for Khanza Repaint with:

- ✅ Dark theme with Red primary color
- ✅ 6 Resource managers (Users, Bookings, Cars, Vouchers, Testimonials, FAQs)
- ✅ Auto-membership tier upgrade system
- ✅ Cloudinary image integration
- ✅ Dashboard widgets with analytics
- ✅ Spatie Shield for role-based permissions

---

## 🚀 INSTALLATION STEPS

### Step 1: Install FilamentPHP

```powershell
# In your Laravel project root
composer require filament/filament:"^3.0" -W

# Install FilamentPHP
php artisan filament:install --panels

# Create the admin panel
php artisan make:filament-panel admin
```

### Step 2: Install Required Packages

```powershell
# Spatie Permissions (for RBAC)
composer require spatie/laravel-permission

# Spatie Media Library (for uploads)
composer require spatie/laravel-medialibrary

# Filament Shield (permissions UI)
composer require bezhansalleh/filament-shield

# Filament Cloudinary Plugin
composer require awcodes/filament-cloudinary

# Database notifications
php artisan migrate
```

### Step 3: Publish Configuration

```powershell
# Publish Spatie Permission tables
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="config"

# Publish Media Library
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"

# Publish Shield
php artisan vendor:publish --provider="BezhanSalleh\FilamentShield\FilamentShieldServiceProvider" --tag="migrations"

# Run migrations
php artisan migrate
```

---

## 🛠️ FILE STRUCTURE

```
app/Filament/
├── Panels/
│   └── AdminPanel.php                    # Main panel configuration
│
├── Resources/
│   ├── UserResource.php                  # Customer management
│   ├── BookingResource.php               # Booking management
│   ├── CarGarageResource.php             # Garage inventory
│   ├── VoucherResource.php               # Discount codes
│   ├── TestimonialResource.php           # Review moderation
│   ├── FAQResource.php                   # FAQ management
│   └── {Resource}/Pages/
│       ├── List{Resource}.php
│       ├── Create{Resource}.php
│       ├── Edit{Resource}.php
│       └── View{Resource}.php
│
└── Widgets/
    ├── ActiveBookingsWidget.php          # Booking stats
    ├── MonthlRevenueWidget.php           # Revenue tracking
    └── ServiceTrendsWidget.php           # Service analytics
```

---

## 📊 KEY FEATURES EXPLAINED

### 1. **Dark Theme with Red Primary Color**

The admin panel is configured with:
- **Primary Color**: #FF0000 (Red)
- **Theme**: Midnight (Dark)
- **Font**: Inter

Configuration in `AdminPanel.php`:
```php
->colors([
    'danger' => Color::Red,
    'primary' => Color::Red,
])
->darkMode(true)
```

### 2. **User Resource with Membership Tracking**

Features:
- View membership tier (None, Bronze, Silver, Gold)
- Track service count and total spending
- Auto-calculate member benefits
- Filter by tier and role

**Membership Logic**:
- None → Bronze: 2-3 services completed
- Bronze → Silver: 4-6 services completed
- Silver → Gold: 7+ services completed

### 3. **Booking Resource with Status Workflow**

Features:
- Create, edit, view bookings
- Status tracking: Pending → Confirmed → In Progress → Completed
- Prevent overlapping bookings (via validation)
- Quick actions to mark completed or cancel
- Apply vouchers and track discounts

**Key Validation**:
```php
// Prevent double-booking same date/customer
$existingBooking = Booking::where('user_id', $userId)
    ->where('scheduled_date', $date)
    ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
    ->first();
```

### 4. **Car Garage Resource with Cloudinary**

Features:
- Upload car images (up to 5)
- Auto-optimize with Cloudinary
- Track vehicle details (brand, year, mileage, etc.)
- Manage inventory status
- View tracking
- Seller information

**Image Upload**:
```php
FileUpload::make('images')
    ->directory('garage/cars')
    ->disk('cloudinary')  // Uses Cloudinary
```

### 5. **Voucher Resource**

Features:
- Generate unique codes
- Fixed or percentage discounts
- Usage limits and expiry dates
- Service-specific applicability
- Duplicate vouchers
- Active/Expired filtering

### 6. **Dashboard Widgets**

#### Active Bookings Widget
- Active bookings count
- Today's schedule
- Completed this month
- Total customers

#### Monthly Revenue Widget
- Current month revenue
- Revenue change vs last month
- Total all-time revenue
- Average booking value

#### Service Trends Widget
- Line chart: Repaint vs General services
- Last 30 days data
- Visual trend analysis

---

## 🔐 SHIELD SETUP (Spatie Permissions)

### Create Roles

```powershell
php artisan shield:create-roles
# Automatically creates: Super Admin, Admin, Staff roles
```

### Configure Roles & Permissions

```powershell
# Create initial Shield database
php artisan shield:setup

# Assign admin to user
php artisan tinker
>>> $user = User::first();
>>> $user->assignRole('super_admin');
>>> exit
```

### Role Permissions

| Role | Permissions |
|------|-------------|
| **Super Admin** | All CRUD + all resources |
| **Staff** | View/Create/Edit bookings, manage testimonials, view reports |
| **Customer** | View own bookings, submit testimonials |

### Check Permissions in Code

```php
// In your Filament resources
if (auth()->user()->hasRole('staff')) {
    // Staff can only see customer bookings
}

if (auth()->user()->hasRole('super_admin')) {
    // Super admin can manage everything
}
```

---

## 🚨 AUTO TIER UPGRADE SYSTEM

### How It Works

1. **Admin marks booking as "Completed"**
2. **BookingObserver listens for status change**
3. **Observer calls `User->recordService()`**
4. **Membership tier automatically updates**
5. **Notification sent to customer**

### Code Flow

**In AdminPanel.php**:
```php
// Register observer
Booking::observe(BookingUpdateObserver::class);
```

**In BookingUpdateObserver.php**:
```php
public function updated(Booking $booking): void
{
    if ($booking->isDirty('status') && $booking->status === 'completed') {
        $booking->user->recordService($booking->total_price);
        // Tier auto-updated by recordService() method
    }
}
```

**In User Model**:
```php
public function recordService($amount): void
{
    $this->service_count++;
    $this->total_spent += $amount;
    $this->updateMembershipTier();
    $this->save();
}

public function updateMembershipTier(): void
{
    if ($this->service_count < 2) {
        $this->membership_tier = 'none';
    } elseif ($this->service_count < 4) {
        $this->membership_tier = 'bronze';
    } elseif ($this->service_count < 7) {
        $this->membership_tier = 'silver';
    } else {
        $this->membership_tier = 'gold';
    }
}
```

---

## ☁️ CLOUDINARY CONFIGURATION

### 1. Setup in .env

```env
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
```

### 2. Configure Filament

In `config/filesystems.php`:
```php
'cloudinary' => [
    'driver' => 'cloudinary',
    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),
],
```

### 3. Upload Configuration

Images automatically:
- Upload to folder: `garage/cars`, `services`, etc.
- Convert to WebP
- Auto-optimize quality
- Enable progressive rendering
- Cache for 1 year

### 4. In Resource Form

```php
FileUpload::make('images')
    ->directory('garage/cars')
    ->disk('cloudinary')
    ->multiple()
    ->maxFiles(5)
```

---

## 🎯 PREVENTING OVERLAPPING BOOKINGS

### Validation Rules

In `BookingResource.php`:
```php
public function rules(): array
{
    return [
        'user_id' => 'required|exists:users,id',
        'service_id' => 'required|exists:services,id',
        'scheduled_date' => [
            'required',
            'date',
            'after_or_equal:today',
            function ($attribute, $value, $fail) {
                $exists = Booking::where('user_id', request('user_id'))
                    ->where('scheduled_date', $value)
                    ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                    ->exists();
                    
                if ($exists) {
                    $fail('User already has a booking on this date.');
                }
            },
        ],
    ];
}
```

### Filament Form Validation

```php
DatePicker::make('scheduled_date')
    ->unique(
        ignoreRecord: true,
        callback: function ($query, $state) {
            return $query->where('user_id', $this->record?->user_id)
                ->where('scheduled_date', $state)
                ->whereIn('status', ['pending', 'confirmed', 'in_progress']);
        }
    )
```

---

## 📱 ACCESSING ADMIN PANEL

### URLs

```
Development:  http://localhost:8000/admin
Production:   https://yourdomain.com/admin
```

### Login

1. Create admin user:
```powershell
php artisan tinker
>>> $admin = User::create([
...     'name' => 'Admin',
...     'email' => 'admin@example.com',
...     'password' => bcrypt('password'),
...     'role' => 'admin',
... ]);
>>> $admin->assignRole('super_admin');
>>> exit
```

2. Login with credentials
3. Access admin dashboard

---

## 🔍 TESTING FEATURES

### Test Auto Tier Upgrade

```powershell
php artisan tinker

# Create test customer
>>> $user = User::factory()->create(['role' => 'customer']);

# Create service
>>> $service = Service::factory()->create();

# Create and complete bookings
>>> $booking1 = Booking::factory()->create(['user_id' => $user->id, 'service_id' => $service->id]);
>>> $booking1->update(['status' => 'completed']);
>>> $user->refresh();
>>> $user->membership_tier  // Should still be 'none'

# Complete 2 more bookings
>>> $booking2 = Booking::factory()->create(['user_id' => $user->id, 'service_id' => $service->id]);
>>> $booking2->update(['status' => 'completed']);
>>> $user->refresh();
>>> $user->membership_tier  // Should be 'bronze' (2 completed)

# Complete more to reach silver
>>> for ($i = 0; $i < 2; $i++) {
    $b = Booking::factory()->create(['user_id' => $user->id]);
    $b->update(['status' => 'completed']);
}
>>> $user->refresh();
>>> $user->membership_tier  // Should be 'silver' (4+ completed)
```

### Test Overlapping Booking Prevention

In Filament Admin:
1. Go to Bookings → Create
2. Select same customer and date
3. Try to create → Should fail with validation error

### Test Cloudinary Upload

In Car Garage Resource:
1. Create/Edit Car
2. Upload image(s)
3. Images should appear optimized in gallery

---

## 📊 FILAMENT COMMANDS

```powershell
# Create new resource
php artisan make:filament-resource ResourceName

# Create new widget
php artisan make:filament-widget WidgetName

# Create new page
php artisan make:filament-page PageName

# Create new policy
php artisan make:policy ResourceNamePolicy --model=ResourceName

# List all Filament commands
php artisan list filament
```

---

## 🔧 CUSTOMIZATION

### Change Dark Mode

In `AdminPanel.php`:
```php
->darkMode(false)  // Light mode
// or
->darkMode(true)   // Dark mode (default)
```

### Change Primary Color

In `AdminPanel.php`:
```php
->colors([
    'primary' => Color::Blue,  // Change from Red
])
```

### Add More Widgets

In `AdminPanel.php`:
```php
->widgets([
    YourCustomWidget::class,
    AnotherWidget::class,
])
```

---

## 🚀 DEPLOYMENT

### Production Checklist

```
□ Set APP_ENV=production
□ Set APP_DEBUG=false
□ Run php artisan config:cache
□ Run php artisan route:cache
□ Run php artisan view:cache
□ Configure Cloudinary credentials
□ Setup Shield roles and permissions
□ Create admin user
□ Test all Filament resources
```

---

## 📚 USEFUL LINKS

- [Filament Documentation](https://filamentphp.com)
- [Spatie Permission Docs](https://spatie.be/docs/laravel-permission)
- [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary)
- [Filament Shield](https://github.com/BezhanSalleh/filament-shield)
- [Cloudinary API](https://cloudinary.com/documentation)

---

## ✅ COMPLETION CHECKLIST

- [ ] FilamentPHP installed and configured
- [ ] Admin panel created and accessible
- [ ] All 6 resources created and functional
- [ ] Cloudinary integration tested
- [ ] Auto tier upgrade system working
- [ ] Dashboard widgets displaying data
- [ ] Shield roles and permissions setup
- [ ] Admin user created
- [ ] All features tested in production-like environment

---

**Khanza Repaint Admin Backend**  
*Built with FilamentPHP v3 - Professional Laravel Admin Framework*
