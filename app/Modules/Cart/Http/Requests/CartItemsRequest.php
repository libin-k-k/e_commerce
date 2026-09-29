<?php

namespace App\Modules\Cart\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartItemsRequest extends FormRequest
{
    public const MaxItems = 100;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:'.self::MaxItems],
            'items.*' => ['integer', 'min:1', 'distinct'],
        ];
    }

    /**
     * @return list<int>
     */
    public function itemIds(): array
    {
        return array_map('intval', array_values($this->validated('items')));
    }
}
