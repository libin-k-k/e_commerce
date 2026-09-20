<?php

namespace App\Modules\Banner\Http\Requests\Admin;

use App\Modules\Banner\Enums\BannerNavigationLink;
use App\Modules\Banner\Enums\BannerPosition;
use App\Modules\Banner\Enums\BannerStyle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBannerRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'position' => ['required', Rule::enum(BannerPosition::class)],
            'style' => ['required', Rule::enum(BannerStyle::class)],
            'web_image' => ['nullable', 'image', 'max:10240'],
            'mobile_image' => ['nullable', 'image', 'max:10240'],
            'navigation_link' => ['required', Rule::enum(BannerNavigationLink::class)],
            'custom_url' => ['nullable', 'string', 'max:255', 'required_if:navigation_link,custom'],
            'category_slug' => ['nullable', 'string', 'max:120', 'required_if:navigation_link,category'],
            'button_text' => ['nullable', 'string', 'max:60'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }
}
