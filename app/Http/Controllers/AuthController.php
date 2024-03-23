<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Jobs\SendEmailVerificationCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'name' => ['required'],
            'password' => ['required', 'confirmed'],
        ]);

        $user = User::query()->create($data)->fresh();

        $token = $user->createToken('email');

        return response()->json([
            'user' => $user,
            'token' => $token->plainTextToken,
        ], HttpStatus::CREATED->value);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'code' => ['required', 'numeric'],
            'password' => ['required', 'confirmed'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        abort_unless(
            $user->verifyResetPasswordCode($data['code']),
            HttpStatus::BAD_REQUEST->value,
            'Wrong code'
        );

        $user->update(['password' => bcrypt($data['password'])]);

        return response()->json([], HttpStatus::NO_CONTENT->value);
    }

    public function forgetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        $user->sendPasswordResetCode(true);

        return response()->json([], HttpStatus::NO_CONTENT->value);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required'],
            'password' => ['required'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        abort_if(
            $user->restricted,
            HttpStatus::FORBIDDEN->value,
            'User has been restricted'
        );

        abort_unless(
            $user != null && Hash::check($data['password'], $user->password),
            HttpStatus::UNAUTHORIZED->value,
            'Incorrect information provided'
        );

        $token = $user->createToken('email');

        return response()->json([
            'user' => $user,
            'token' => $token->plainTextToken,
        ], HttpStatus::OK->value);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'password' => ['required'],
            'new_password' => ['required', 'confirmed'],
            'logout_all_other_devices' => ['sometimes', 'boolean'],
        ]);

        $user = $request->user();

        abort_unless(
            Hash::check($data['password'], $user->password),
            HttpStatus::FORBIDDEN->value,
            'Current password is incorrect'
        );

        $user->update(['password', $data['password']]);

        if ($data['logout_all_other_devices'] ?? false) {
            $currentRequestPersonalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            $user->tokens()->where('id', '!=', $currentRequestPersonalAccessToken->id)->delete();
        }

        return response()->json([], HttpStatus::NO_CONTENT->value);
    }

    public function emailVerification(Request $request)
    {
        SendEmailVerificationCode::dispatch($request->user());

        return response()->json([], HttpStatus::NO_CONTENT->value);
    }

    public function verifyEmail(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'numeric'],
        ]);

        abort_unless(
            $request->user()->verifyEmailViaCode($data['code']),
            HttpStatus::BAD_REQUEST->value,
            'Wrong Code'
        );

        $request->user()->markEmailAsVerified();

        return response()->json([], HttpStatus::NO_CONTENT->value);
    }
}
