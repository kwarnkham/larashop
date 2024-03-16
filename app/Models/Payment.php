<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Payment extends BaseModel
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'type' => PaymentType::class
        ];
    }

    public function payable()
    {
        return $this->morphTo();
    }
}
