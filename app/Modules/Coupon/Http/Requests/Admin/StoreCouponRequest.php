<?php

namespace App\Modules\Coupon\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCouponRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:60'],
            'name' => ['nullable', 'string', 'max:160'],
            'type' => ['required', 'string', Rule::in(['percent', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0.01'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_order_amount' => ['nullable', 'numeric', 'min:0'],
            'has_validity' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'total_usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_user_usage_limit' => ['nullable', 'integer', 'min:1'],
            'first_order_only' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'has_validity' => $this->boolean('has_validity'),
            'first_order_only' => $this->boolean('first_order_only'),
            'is_active' => $this->boolean('is_active', true),
            'code' => strtoupper(trim((string) $this->input('code', ''))),
        ]);
    }
}
