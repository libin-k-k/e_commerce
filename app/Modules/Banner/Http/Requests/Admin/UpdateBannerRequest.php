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
            'display_width' => ['nullable', 'integer', 'min:40', 'max:4000'],
            'display_height' => ['nullable', 'integer', 'min:40', 'max:4000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->input('sort_order', 0),
            'display_width' => $this->filled('display_width') ? $this->input('display_width') : null,
            'display_height' => $this->filled('display_height') ? $this->input('display_height') : null,
        ]);
    }
}
