<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoucherClaim extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'voucher_id',
        'user_id',
        'booking_id',
        'claimed_at',
        'status',
        'used_at',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Get the voucher
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * Get the user who claimed
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the booking (if used)
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class)->withTrashed();
    }

    /**
     * Scope for pending claims
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for used claims
     */
    public function scopeUsed($query)
    {
        return $query->where('status', 'used');
    }

    /**
     * Mark as used
     */
    public function markAsUsed(?int $bookingId = null): void
    {
        $this->update([
            'status' => 'used',
            'booking_id' => $bookingId,
            'used_at' => now(),
        ]);
    }

    /**
     * Check for duplicate claims (prevent abuse)
     */
    public static function hasDuplicateClaim(int $voucherId, int $userId): bool
    {
        return self::where('voucher_id', $voucherId)
                   ->where('user_id', $userId)
                   ->exists();
    }
}
