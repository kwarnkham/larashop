<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    const PER_PAGE = 20;

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:items'],
            'decription' => ['sometimes', 'max:255']
        ]);

        $item = Item::query()->create($data);

        return response()->json($item, HttpStatus::CREATED->value);
    }

    public function index(Request $request)
    {
        $query = Item::query();
        return response()
            ->json(
                [
                    'pagination' => $query->paginate($request->per_page ?? ItemController::PER_PAGE)
                ],
                HttpStatus::OK->value
            );
    }

    public function find(Request $request, Item $item)
    {
        return response()
            ->json(
                $item,
                HttpStatus::OK->value
            );
    }
}
