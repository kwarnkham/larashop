<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function uploadPicture(Request $request)
    {
        $data = $request->validate([
            'picture' => ['required', 'image'],
        ]);

        $user = $request->user();

        $picture = Storage::putFile('users', $data['picture']);

        abort_if(! $picture, HttpStatus::BAD_REQUEST->value, 'Cannot upload the picture');

        $user->update(['picture' => $picture]);

        return response()->json($user);
    }
}
