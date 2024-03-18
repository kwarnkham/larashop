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
}
