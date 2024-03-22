<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
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
        ]);

        $address = $request->user()->addresses()->create($data);

        return response()->json($address, HttpStatus::CREATED->value);
    }
}
