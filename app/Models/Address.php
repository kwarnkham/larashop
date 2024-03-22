<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Address extends BaseModel
{
    use HasFactory;

    protected static function booted(): void
    {
        static::created(function (Address $address) {
            DB::table('addresses')->where([
                ['user_id', '=', $address->user_id],
                ['id', '!=', $address->id],
            ])->update(['default' => false]);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
