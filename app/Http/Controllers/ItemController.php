<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:items'],
            'decription' => ['sometimes', 'max:255']
        ]);

        $item = Item::query()->create($data);

        return response()->json($item, HttpStatus::CREATED->value);
    }
}
