<?php

namespace App\Observers;

use App\Models\Booking;

class BookingObserver
{
    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        // Log booking creation for auditing
        activity('booking')
            ->performedOn($booking)
            ->causedBy($booking->user)
            ->log('created');
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        // Log any status changes
        activity('booking')
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->log('updated');
    }

    /**
     * Handle the Booking "deleted" event.
     */
    public function deleted(Booking $booking): void
    {
        activity('booking')
            ->performedOn($booking)
            ->causedBy(auth()->user())
            ->log('deleted');
    }
}
