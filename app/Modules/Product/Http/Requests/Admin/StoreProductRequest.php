<?php

namespace App\Modules\Product\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
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
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash'],
            'sku' => ['required', 'string', 'max:60'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->whereNull('parent_id')],
            'subcategory_id' => ['nullable', 'integer', 'exists:categories,id'],
            'main_image' => ['nullable', 'image', 'max:10240'],
            'additional_images' => ['nullable', 'array'],
            'additional_images.*' => ['image', 'max:10240'],
            'sizes' => ['nullable'],
            'colors' => ['nullable'],
            'variants' => ['nullable'],
            'is_unlaunched' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $sub = $this->input('subcategory_id');

        $this->merge([
            'is_unlaunched' => $this->boolean('is_unlaunched'),
            'sort_order' => $this->input('sort_order', 0),
            'rating' => $this->input('rating', 0),
            'subcategory_id' => ($sub === '' || $sub === 'null') ? null : $sub,
        ]);
    }
}
