<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends BaseModel
{
    use HasFactory;

    public function payable()
    {
        return $this->morphTo();
    }
}
