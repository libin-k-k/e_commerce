<?php

namespace App\Modules\Offer\Http\Requests\Admin;

use App\Modules\Offer\Models\OfferCampaign;
use Illuminate\Foundation\Http\FormRequest;

class StoreOfferCampaignRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:80'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'countdown_enabled' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'product_ids' => ['nullable', 'array', 'max:'.OfferCampaign::ProductLimit],
            'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'countdown_enabled' => $this->boolean('countdown_enabled'),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->input('sort_order', 0),
            'starts_at' => $this->filled('starts_at') ? $this->input('starts_at') : null,
            'ends_at' => $this->filled('ends_at') ? $this->input('ends_at') : null,
            'product_ids' => array_values(array_filter((array) $this->input('product_ids', []))),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_ids.max' => 'Choose up to '.OfferCampaign::ProductLimit.' products for this card.',
        ];
    }
}
