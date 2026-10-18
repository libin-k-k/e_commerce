<?php

namespace App\Modules\Account\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteGoogleRegistrationRequest extends FormRequest
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
            'mobile' => [
                'required',
                'string',
                'digits_between:10,15',
                Rule::unique('users', 'mobile')->ignore($userId),
            ],
            'addresses' => ['required', 'array', 'min:1'],
            'addresses.*.label' => ['required', 'string', 'max:40'],
            'addresses.*.full_address' => ['required', 'string', 'max:500'],
            'addresses.*.pincode' => ['required', 'string', 'max:12'],
            'addresses.*.district' => ['required', 'string', 'max:120'],
            'addresses.*.state' => ['required', 'string', 'max:120'],
            'addresses.*.is_default' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $mobile = preg_replace('/\D+/', '', (string) $this->input('mobile', '')) ?: '';

        $this->merge([
            'mobile' => $mobile,
        ]);
    }

    /**
     * @return array{
     *     name: string,
     *     mobile: string,
     *     addresses: list<array{
     *         label: string,
     *         full_address: string,
     *         pincode: string,
     *         district: string,
     *         state: string,
     *         is_default?: bool
     *     }>
     * }
     */
    public function validatedPayload(): array
    {
        /** @var array{name: string, mobile: string, addresses: list<array<string, mixed>>} $data */
        $data = $this->validated();

        return $data;
    }
}
