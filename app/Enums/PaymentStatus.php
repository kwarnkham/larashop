<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Canceled = 'canceled';

    public static function all()
    {
        return ['pending', 'processing', 'completed', 'canceled'];
    }
}
