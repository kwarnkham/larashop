<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    const PER_PAGE = 20;

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

    public function index(Request $request)
    {
        $query = User::query()->latest('id');

        return response()->json([
            'pagination' => $query->paginate($request->per_page ?? UserController::PER_PAGE),
        ]);
    }

    public function find(Request $request, User $user)
    {
        return response()->json($user);
    }

    public function toggleRestriction(Request $request, User $user)
    {
        $user->update(['restricted' => ! $user->restricted]);

        return response()->json($user);
    }
}
