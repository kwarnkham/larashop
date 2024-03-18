<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
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

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required'],
            'password' => ['required'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

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
            'logout_all_other_devices' => ['sometimes', 'boolean']
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
}
