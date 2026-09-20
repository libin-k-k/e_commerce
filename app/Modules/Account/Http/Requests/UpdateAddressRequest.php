<?php

namespace App\Modules\Account\Http\Requests;

use App\Modules\Account\Models\UserAddress;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        $address = $this->route('address');

        return $address instanceof UserAddress
            && $this->user() !== null
            && (int) $address->user_id === (int) $this->user()->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:40'],
            'full_address' => ['required', 'string', 'max:500'],
            'pincode' => ['required', 'string', 'max:12'],
            'district' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_default' => $this->boolean('is_default'),
            'label' => trim((string) $this->input('label', '')),
            'full_address' => trim((string) $this->input('full_address', '')),
            'pincode' => trim((string) $this->input('pincode', '')),
            'district' => trim((string) $this->input('district', '')),
            'state' => trim((string) $this->input('state', '')),
        ]);
    }
}
