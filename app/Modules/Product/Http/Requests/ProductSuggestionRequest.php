<?php

namespace App\Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductSuggestionRequest extends FormRequest
{
    public const MinLength = 2;

    public const MaxLength = 100;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Search-as-you-type input is trimmed and clipped rather than rejected.
     */
    protected function prepareForValidation(): void
    {
        $term = $this->query('q');

        $this->merge([
            'q' => is_string($term) ? mb_substr(trim($term), 0, self::MaxLength) : '',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['present', 'string', 'max:'.self::MaxLength],
        ];
    }

    public function term(): string
    {
        return (string) $this->validated('q');
    }
}
