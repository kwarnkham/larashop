<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, SoftDeletes, Notifiable;

    protected $guarded = [''];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $role): bool
    {
        return once(fn () => $this->roles()->where('name', $role)->exists());
    }

    public function getPasswordResetCode()
    {
        return Cache::remember($this->id . ".password_reset_code", 60, function () {
            $codeLength = 6;
            $code = '';

            while (strlen($code) < $codeLength) {
                $code .= rand(0, 9);
            }
            return $code;
        });
    }

    public function refreshPasswordResetCode()
    {
        return Cache::forget($this->id . ".password_reset_code");
    }

    public function getEmailVerificationCode()
    {
        return Cache::remember($this->id . ".email_verification_code", 60, function () {
            $codeLength = 6;
            $code = '';

            while (strlen($code) < $codeLength) {
                $code .= rand(0, 9);
            }
            return $code;
        });
    }

    public function refreshEmailVerificationCode()
    {
        return Cache::forget($this->id . ".email_verification_code");
    }

    public function verifyEmailViaCode(string $code): bool
    {
        if ($code != $this->getEmailVerificationCode()) return false;
        return $this->refreshEmailVerificationCode();
    }

    public function verifyResetPasswordCode(string $code): bool
    {
        if ($code != $this->getPasswordResetCode()) return false;
        return $this->refreshPasswordResetCode();
    }

    public function sendPasswordResetCode()
    {
        $this->notify(new ResetPassword);
    }
}
