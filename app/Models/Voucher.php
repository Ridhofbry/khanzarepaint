<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'description',
        'discount_amount',
        'discount_percentage',
        'max_uses',
        'current_uses',
        'usage_per_user',
        'is_active',
        'expires_at',
        'starts_at',
        'minimum_purchase_amount',
        'applicable_to',
        'applicable_services',
    ];

    protected $casts = [
        'discount_amount' => 'integer',
        'discount_percentage' => 'integer',
        'max_uses' => 'integer',
        'current_uses' => 'integer',
        'usage_per_user' => 'integer',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'starts_at' => 'datetime',
        'minimum_purchase_amount' => 'integer',
        'applicable_services' => 'json',
    ];

    /**
     * Get all claims for this voucher
     */
    public function claims(): HasMany
    {
        return $this->hasMany(VoucherClaim::class);
    }

    /**
     * Scope to get active and valid vouchers
     */
    public function scopeValid($query)
    {
        return $query->where('is_active', true)
                    ->where('starts_at', '<=', now())
                    ->where('expires_at', '>=', now())
                    ->where(function ($q) {
                        $q->whereNull('max_uses')
                          ->orWhereRaw('current_uses < max_uses');
                    });
    }

    /**
     * Scope to get expired vouchers
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Check if voucher can be used
     */
    public function canBeUsed(int $purchaseAmount = 0): bool
    {
        // Check if active and within date range
        if (!$this->is_active || $this->starts_at > now() || $this->expires_at < now()) {
            return false;
        }

        // Check max uses
        if ($this->max_uses && $this->current_uses >= $this->max_uses) {
            return false;
        }

        // Check minimum purchase
        if ($purchaseAmount < $this->minimum_purchase_amount) {
            return false;
        }

        return true;
    }

    /**
     * Generate unique voucher code
     */
    public static function generateCode(): string
    {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount(int $purchaseAmount): int
    {
        if ($this->discount_amount) {
            return min($this->discount_amount, $purchaseAmount);
        }

        if ($this->discount_percentage) {
            return intval(($purchaseAmount * $this->discount_percentage) / 100);
        }

        return 0;
    }

    /**
     * Increment usage count
     */
    public function recordUsage(): void
    {
        $this->increment('current_uses');
    }

    /**
     * Check if voucher is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }
}
