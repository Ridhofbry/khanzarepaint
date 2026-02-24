<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}