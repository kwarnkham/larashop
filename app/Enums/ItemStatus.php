<?php

namespace App\Enums;

enum ItemStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public static function all()
    {
        return ['active', 'inactive'];
    }
}
