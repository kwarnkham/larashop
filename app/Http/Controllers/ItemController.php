<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Enums\ItemStatus;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    const PER_PAGE = 20;

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:items'],
            'description' => ['sometimes', 'max:255'],
            'price' => ['required', 'numeric'],
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
                    'pagination' => $query->paginate($request->per_page ?? ItemController::PER_PAGE),
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

    public function update(Request $request, Item $item)
    {
        $data = $request->validate([
            'name' => ['required', Rule::unique('items', 'name')->ignoreModel($item)],
            'description' => ['sometimes', 'max:255'],
            'price' => ['required', 'numeric'],
            'status' => ['required', Rule::in(ItemStatus::all())],
        ]);

        $item->update($data);

        return response()->json($item, HttpStatus::OK->value);
    }

    public function destroy(Request $request, Item $item)
    {
        $item->delete();

        return response()->json([], HttpStatus::NO_CONTENT->value);
    }
}
