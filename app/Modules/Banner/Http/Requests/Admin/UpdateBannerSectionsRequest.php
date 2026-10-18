<?php

namespace App\Modules\Banner\Http\Requests\Admin;

use App\Modules\Banner\Enums\BannerPosition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBannerSectionsRequest extends FormRequest
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
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.position' => ['required', 'distinct', Rule::enum(BannerPosition::class)],
            'sections.*.is_enabled' => ['required', 'boolean'],
            'sections.*.show_count' => ['required', 'boolean'],
        ];
    }
}
