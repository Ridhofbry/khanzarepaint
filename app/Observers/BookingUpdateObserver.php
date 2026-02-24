<?php

namespace App\Observers;

use App\Models\Booking;
use App\Models\User;
use App\Models\VoucherClaim;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\MembershipTierUpgradedNotification;

class BookingObserver
{
    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        Log::info('Booking created', [
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'service_id' => $booking->service_id,
            'total_price' => $booking->total_price,
        ]);
    }

    /**
     * Handle the Booking "updated" event.
     * This is where we handle auto-tier upgrade on completion
     */
    public function updated(Booking $booking): void
    {
        // Only process if status changed to 'completed'
        if ($booking->isDirty('status') && $booking->status === 'completed') {
            $this->handleBookingCompletion($booking);
        }
    }

    /**
     * Handle booking completion - auto update member tier
     */
    private function handleBookingCompletion(Booking $booking): void
    {
        try {
            $user = $booking->user;

            // Record the service
            $user->recordService($booking->total_price);

            // Get updated user instance to check new tier
            $user->refresh();

            Log::info('Booking completed - member updated', [
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'service_count' => $user->service_count,
                'membership_tier' => $user->membership_tier,
            ]);

            // Send notification if tier was upgraded
            if ($booking->wasChanged('membership_tier')) {
                $this->notifyTierUpgrade($user);
            }

        } catch (\Exception $e) {
            Log::error('Error processing booking completion', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send notification when member tier is upgraded
     */
    private function notifyTierUpgrade(User $user): void
    {
        try {
            $tierName = ucfirst($user->membership_tier);
            $discount = $user->getMembershipDiscount();

            Log::info('Sending tier upgrade notification', [
                'user_id' => $user->id,
                'new_tier' => $user->membership_tier,
            ]);

            // Send email notification
            Notification::send($user, new MembershipTierUpgradedNotification(
                $tierName,
                $discount
            ));

        } catch (\Exception $e) {
            Log::error('Error sending tier upgrade notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Booking "restored" event.
     */
    public function restored(Booking $booking): void
    {
        Log::info('Booking restored', [
            'booking_id' => $booking->id,
        ]);
    }

    /**
     * Handle the Booking "force deleted" event.
     */
    public function forceDeleted(Booking $booking): void
    {
        Log::warning('Booking force deleted', [
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
        ]);
    }
}
