<?php

namespace App\Models;

use App\Enums\MembershipTier;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

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
        'membership_tier' => MembershipTier::class,
    ];

    /**
     * Get all bookings for this user
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Record a completed service and update tier
     */
    public function recordService(int $amount): void
    {
        $this->increment('service_count');
        $this->increment('total_spent', $amount);
        $this->updateMembershipTier();
    }

    /**
     * Update membership tier based on service count
     * Logic:
     * - 1-2 services: Bronze
     * - 3-4 services: Silver
     * - 5+ services: Gold
     */
    public function updateMembershipTier(): void
    {
        $serviceCount = $this->service_count;
        $newTier = null;

        if ($serviceCount >= 5) {
            $newTier = MembershipTier::Gold;
        } elseif ($serviceCount >= 3) {
            $newTier = MembershipTier::Silver;
        } elseif ($serviceCount >= 1) {
            $newTier = MembershipTier::Bronze;
        }

        if ($this->membership_tier !== $newTier) {
            $this->update(['membership_tier' => $newTier]);
        }
    }
    
    public function isStaff(): bool
    {
        return $this->hasAnyRole(['super_admin', 'staff']);
    }
}
