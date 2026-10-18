<?php

namespace App\Modules\Banner\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReorderBannersRequest extends FormRequest
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
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:banners,id'],
        ];
    }
}
