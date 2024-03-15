<?php

namespace App\Enums;

enum PaymentType: string
{
    case Larapay = 'larapay';

    public static function all()
    {
        return ['larapay'];
    }
}
