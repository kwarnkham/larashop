<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Paid = 'paid';
    case Completed = 'completed';
    case Canceled = 'canceled';

    public static function all()
    {
        return ['pending', 'confirmed', 'paid', 'completed', 'canceled'];
    }
}
