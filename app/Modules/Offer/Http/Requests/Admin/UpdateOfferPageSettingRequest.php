<?php

namespace App\Modules\Offer\Http\Requests\Admin;

use App\Modules\Offer\Enums\OfferTheme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOfferPageSettingRequest extends FormRequest
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
            'section_title' => ['required', 'string', 'max:80'],
            'section_subtitle' => ['nullable', 'string', 'max:160'],
            'eyebrow' => ['required', 'string', 'max:40'],
            'theme' => ['required', Rule::enum(OfferTheme::class)],
            'countdown_enabled' => ['sometimes', 'boolean'],
            'countdown_ends_at' => ['nullable', 'date', 'required_if:countdown_enabled,true,1'],
            'is_enabled' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'section_subtitle' => $this->filled('section_subtitle') ? $this->input('section_subtitle') : null,
            'countdown_enabled' => $this->boolean('countdown_enabled'),
            'countdown_ends_at' => $this->filled('countdown_ends_at') ? $this->input('countdown_ends_at') : null,
            'is_enabled' => $this->boolean('is_enabled'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'countdown_ends_at.required_if' => 'Set an end time when the countdown is on.',
        ];
    }
}
