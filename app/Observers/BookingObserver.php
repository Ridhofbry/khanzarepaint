<?php

namespace App\Observers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Validation\ValidationException;

class BookingObserver
{
    public function creating(Booking $booking): void
    {
        $this->validateNoOverlappingBookings($booking);
    }

    public function updating(Booking $booking): void
    {
        if ($booking->isDirty('status') && $booking->status === BookingStatus::Completed) {
            $booking->user->recordService($booking->total_price);
        }
        
        if ($booking->isDirty('scheduled_date')) {
            $this->validateNoOverlappingBookings($booking);
        }
    }
    
    private function validateNoOverlappingBookings(Booking $booking): void
    {
        $exists = Booking::where('service_id', $booking->service_id)
            ->where('scheduled_date', $booking->scheduled_date)
            ->where('id', '!=', $booking->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'scheduled_date' => 'An overlapping booking already exists for this service on the selected date.',
            ]);
        }
    }
}
