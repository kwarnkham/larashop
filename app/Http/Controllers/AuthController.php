<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Models\User;
use Illuminate\Http\Request;

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
}
