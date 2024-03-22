<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AddressController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', Rule::unique('addresses', 'name')->where('user_id', $user->id)],
            'country' => ['required'],
            'province' => ['required'],
            'township' => ['required'],
            'city' => ['required'],
            'street_one' => ['required'],
            'street_two' => ['sometimes', 'required'],
            'street_three' => ['sometimes', 'required'],
            'street_four' => ['sometimes', 'required'],
            'phone' => ['required'],
            'zip_code' => ['sometimes', 'required'],
            'default' => ['sometimes', 'required', 'boolean'],
        ]);

        $address = $user->addresses()->create($data)->fresh();

        return response()->json($address, HttpStatus::CREATED->value);
    }
}
