<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'role',
        'membership_tier',
        'service_count',
        'total_spent',
        'profile_image',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'service_count' => 'integer',
        'total_spent' => 'integer',
    ];

    /**
     * Get all bookings for this user
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get all cars listed by this user
     */
    public function carsListed(): HasMany
    {
        return $this->hasMany(Car::class, 'seller_id');
    }

    /**
     * Get all testimonials from this user
     */
    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    /**
     * Get all voucher claims
     */
    public function voucherClaims(): HasMany
    {
        return $this->hasMany(VoucherClaim::class);
    }

    /**
     * Get active bookings through bookings relationship
     */
    public function activeBookings(): HasMany
    {
        return $this->bookings()->whereIn('status', ['pending', 'confirmed', 'in_progress']);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is garage owner
     */
    public function isGarageOwner(): bool
    {
        return $this->role === 'garage_owner';
    }

    /**
     * Check if user is customer
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Update membership tier based on service count
     * Bronze: 2-3 services
     * Silver: 4-6 services
     * Gold: 7+ services
     */
    public function updateMembershipTier(): void
    {
        $newTier = match ($this->service_count) {
            0, 1 => 'none',
            2, 3 => 'bronze',
            4, 5, 6 => 'silver',
            default => 'gold',
        };

        if ($newTier !== $this->membership_tier) {
            $this->update(['membership_tier' => $newTier]);
        }
    }

    /**
     * Get discount percentage based on membership tier
     */
    public function getMembershipDiscount(): int
    {
        return match ($this->membership_tier) {
            'bronze' => 5,
            'silver' => 10,
            'gold' => 15,
            default => 0,
        };
    }

    /**
     * Auto-update membership tier based on service count
     * Logic:
     * - None: 0-1 services
     * - Bronze: 2-3 services (5% discount)
     * - Silver: 4-6 services (10% discount)
     * - Gold: 7+ services (15% discount)
     */
    public function updateMembershipTier(): void
    {
        $oldTier = $this->membership_tier ?? 'none';

        if ($this->service_count < 2) {
            $this->membership_tier = 'none';
        } elseif ($this->service_count < 4) {
            $this->membership_tier = 'bronze';
        } elseif ($this->service_count < 7) {
            $this->membership_tier = 'silver';
        } else {
            $this->membership_tier = 'gold';
        }

        // Log tier upgrade if changed
        if ($oldTier !== $this->membership_tier && $this->membership_tier !== 'none') {
            \Illuminate\Support\Facades\Log::info('Member tier upgraded', [
                'user_id' => $this->id,
                'old_tier' => $oldTier,
                'new_tier' => $this->membership_tier,
                'service_count' => $this->service_count,
            ]);
        }
    }

    /**
     * Record a completed service and update tier
     */
    public function recordService(int $amount): void
    {
        $this->service_count++;
        $this->total_spent += $amount;
        $this->updateMembershipTier();
        $this->save();
    }

    /**
     * Get tier display name
     */
    public function getTierDisplayName(): string
    {
        return match ($this->membership_tier) {
            'bronze' => 'Bronze Member',
            'silver' => 'Silver Member',
            'gold' => 'Gold Member',
            default => 'Regular Customer',
        };
    }

    /**
     * Check if user is staff
     */
    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'garage_owner']) || $this->hasAnyRole(['staff', 'admin', 'super_admin']);
    }

    /**
     * Record a service booking and update membership
     */
    public function recordService(int $amount): void
    {
        $this->increment('service_count');
        $this->increment('total_spent', $amount);
        $this->updateMembershipTier();
    }

    /**
     * Format total spent
     */
    public function getFormattedTotalSpentAttribute(): string
    {
        return 'Rp ' . number_format($this->total_spent / 100, 0, ',', '.');
    }

    /**
     * Scope for garage owners
     */
    public function scopeGarageOwners($query)
    {
        return $query->where('role', 'garage_owner');
    }

    /**
     * Scope for admins
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope for customers
     */
    public function scopeCustomers($query)
    {
        return $query->where('role', 'customer');
    }

    /**
     * Get users with specific membership tier
     */
    public function scopeMembershipTier($query, string $tier)
    {
        return $query->where('membership_tier', $tier);
    }

    /**
     * Update last login
     */
    public function recordLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }
}
