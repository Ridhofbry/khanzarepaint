<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BookingService
{
    /**
     * Create a new booking with atomic transaction to prevent double booking
     *
     * @param User $user
     * @param Service $service
     * @param string $scheduledDate
     * @param string|null $notes
     * @param int|null $voucherId
     * @return array
     * @throws Exception
     */
    public function createBooking(
        User $user,
        Service $service,
        string $scheduledDate,
        ?string $notes = null,
        ?int $voucherId = null
    ): array {
        try {
            return DB::transaction(function () use ($user, $service, $scheduledDate, $notes, $voucherId) {
                // Lock the user row to prevent race conditions
                $lockedUser = User::lockForUpdate()->find($user->id);

                // Check for existing bookings at same time
                $existingBooking = Booking::lockForUpdate()
                    ->where('user_id', $user->id)
                    ->where('scheduled_date', $scheduledDate)
                    ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                    ->first();

                if ($existingBooking) {
                    throw new Exception('User already has a booking at this time');
                }

                // Calculate pricing
                $baseprice = $service->price;
                $discountAmount = 0;
                $finalPrice = $basePrice;
                $voucherUsed = null;

                // Apply voucher if provided
                if ($voucherId) {
                    $voucher = Voucher::lockForUpdate()->find($voucherId);

                    if (!$voucher || !$voucher->canBeUsed($basePrice)) {
                        throw new Exception('Invalid or expired voucher');
                    }

                    // Check duplicate claim prevention
                    $hasClaim = $lockedUser->voucherClaims()
                        ->where('voucher_id', $voucherId)
                        ->where('status', 'pending')
                        ->exists();

                    if ($hasClaim) {
                        throw new Exception('User has already claimed this voucher');
                    }

                    $discountAmount = $voucher->calculateDiscount($basePrice);
                    $finalPrice = $basePrice - $discountAmount;
                    $voucherUsed = $voucherId;

                    // Increment voucher usage
                    $voucher->recordUsage();
                }

                // Create the booking
                $booking = Booking::create([
                    'user_id' => $user->id,
                    'service_id' => $service->id,
                    'scheduled_date' => $scheduledDate,
                    'status' => 'pending',
                    'notes' => $notes,
                    'total_price' => $finalPrice,
                    'booking_code' => Booking::generateBookingCode(),
                    'voucher_id' => $voucherUsed,
                    'discount_amount' => $discountAmount,
                ]);

                // Record service for membership
                $lockedUser->recordService($finalPrice);

                Log::info('Booking created successfully', [
                    'booking_id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'user_id' => $user->id,
                    'service_id' => $service->id,
                    'discount' => $discountAmount,
                ]);

                return [
                    'success' => true,
                    'booking' => $booking,
                    'message' => 'Booking created successfully',
                ];
            }, attempts: 3);
        } catch (Exception $e) {
            Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'service_id' => $service->id,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'booking' => null,
            ];
        }
    }

    /**
     * Cancel a booking
     *
     * @param Booking $booking
     * @param string|null $reason
     * @return array
     */
    public function cancelBooking(Booking $booking, ?string $reason = null): array
    {
        try {
            return DB::transaction(function () use ($booking, $reason) {
                if (!$booking->canBeCancelled()) {
                    throw new Exception('Booking cannot be cancelled at this time');
                }

                $booking->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => $reason,
                ]);

                // Restore voucher usage if any
                if ($booking->voucher_id) {
                    $voucher = Voucher::find($booking->voucher_id);
                    if ($voucher) {
                        $voucher->decrement('current_uses');
                    }
                }

                Log::info('Booking cancelled', [
                    'booking_id' => $booking->id,
                    'reason' => $reason,
                ]);

                return [
                    'success' => true,
                    'message' => 'Booking cancelled successfully',
                ];
            });
        } catch (Exception $e) {
            Log::error('Booking cancellation failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Confirm a booking
     *
     * @param Booking $booking
     * @return array
     */
    public function confirmBooking(Booking $booking): array
    {
        try {
            if ($booking->status !== 'pending') {
                throw new Exception('Only pending bookings can be confirmed');
            }

            $booking->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

            Log::info('Booking confirmed', ['booking_id' => $booking->id]);

            return [
                'success' => true,
                'message' => 'Booking confirmed successfully',
            ];
        } catch (Exception $e) {
            Log::error('Booking confirmation failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Complete a booking
     *
     * @param Booking $booking
     * @return array
     */
    public function completeBooking(Booking $booking): array
    {
        try {
            if ($booking->status !== 'in_progress') {
                throw new Exception('Only in-progress bookings can be completed');
            }

            $booking->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Mark voucher as used if applicable
            if ($booking->voucher_id) {
                $claim = $booking->user->voucherClaims()
                    ->where('voucher_id', $booking->voucher_id)
                    ->first();

                if ($claim) {
                    $claim->markAsUsed($booking->id);
                }
            }

            Log::info('Booking completed', ['booking_id' => $booking->id]);

            return [
                'success' => true,
                'message' => 'Booking completed successfully',
            ];
        } catch (Exception $e) {
            Log::error('Booking completion failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get available time slots for a given date
     *
     * @param string $date
     * @param int $slotDurationMinutes
     * @return array
     */
    public function getAvailableSlots(string $date, int $slotDurationMinutes = 60): array
    {
        $businessHoursStart = 9; // 9 AM
        $businessHoursEnd = 17; // 5 PM
        $slots = [];

        // Get all bookings for the date
        $bookings = Booking::whereDate('scheduled_date', $date)
            ->where('status', '!=', 'cancelled')
            ->get()
            ->groupBy(function ($booking) {
                return $booking->scheduled_date->format('H:i');
            });

        // Generate available slots
        for ($hour = $businessHoursStart; $hour < $businessHoursEnd; $hour++) {
            for ($minute = 0; $minute < 60; $minute += $slotDurationMinutes) {
                $time = sprintf('%02d:%02d', $hour, $minute);

                if (!isset($bookings[$time])) {
                    $slots[] = $time;
                }
            }
        }

        return $slots;
    }
}
