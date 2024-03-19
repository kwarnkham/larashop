<?php

namespace App\Http\Requests;

use App\Enums\ItemStatus;
use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SubmitOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function getSubmittedItems()
    {
        return once(function () {
            return Item::query()->whereIn('id', array_map(
                fn ($value) => $value['id'],
                $this->items
            ))->get();
        });
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $submittedItems = $this->getSubmittedItems();

                $submittedItems->each(function ($submittedItem, $key) use ($validator) {
                    if ($submittedItem->status == ItemStatus::Inactive) {
                        $validator->errors()->add(
                            'items.'.$key,
                            "The item '{$submittedItem->name}' is inactive."
                        );
                    }
                });
            },
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:items'],
            'items.*.quantity' => ['required', 'numeric'],
        ];
    }
}
