<?php

namespace App\Modules\Account\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'mobile' => [
                'required',
                'string',
                'digits_between:10,15',
                Rule::unique('users', 'mobile')->ignore($userId),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $mobile = preg_replace('/\D+/', '', (string) $this->input('mobile', '')) ?: '';
        $email = trim((string) $this->input('email', ''));

        $this->merge([
            'mobile' => $mobile,
            'email' => $email !== '' ? $email : null,
        ]);
    }
}
