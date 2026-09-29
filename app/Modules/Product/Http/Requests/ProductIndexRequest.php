<?php

namespace App\Modules\Product\Http\Requests;

use App\Modules\Product\Enums\PriceRange;
use App\Modules\Product\Enums\ProductSort;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductIndexRequest extends FormRequest
{
    public const RatingOptions = [4, 3];

    public const PerPageOptions = [12, 24, 48];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Unknown or malformed filter values are dropped instead of failing,
     * so a stale or hand-edited listing URL still renders.
     */
    protected function prepareForValidation(): void
    {
        $minPrice = $this->wholeNumber('min_price');
        $maxPrice = $this->wholeNumber('max_price');

        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $this->merge([
            'category' => $this->cleanString('category', 120),
            'q' => $this->cleanString('q', 100),
            'size' => $this->cleanString('size', 30),
            'color' => $this->cleanString('color', 40),
            'sort' => ProductSort::tryFrom((string) $this->cleanString('sort', 20))?->value,
            'price' => PriceRange::tryFrom((string) $this->cleanString('price', 20))?->value,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'rating' => in_array((int) $this->query('rating'), self::RatingOptions, true)
                ? (int) $this->query('rating')
                : null,
            'page' => max(1, (int) $this->wholeNumber('page')),
            'per_page' => in_array((int) $this->query('per_page'), self::PerPageOptions, true)
                ? (int) $this->query('per_page')
                : self::PerPageOptions[0],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', 'max:120'],
            'q' => ['nullable', 'string', 'max:100'],
            'size' => ['nullable', 'string', 'max:30'],
            'color' => ['nullable', 'string', 'max:40'],
            'sort' => ['nullable', Rule::enum(ProductSort::class)],
            'price' => ['nullable', Rule::enum(PriceRange::class)],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0'],
            'rating' => ['nullable', 'integer', Rule::in(self::RatingOptions)],
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', Rule::in(self::PerPageOptions)],
        ];
    }

    /**
     * @return array{category: ?string, q: ?string, size: ?string, color: ?string, sort: ProductSort, price: ?PriceRange, minPrice: ?int, maxPrice: ?int, rating: ?int, page: int, perPage: int}
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'category' => $validated['category'] ?? null,
            'q' => $validated['q'] ?? null,
            'size' => $validated['size'] ?? null,
            'color' => $validated['color'] ?? null,
            'sort' => ProductSort::tryFrom((string) ($validated['sort'] ?? '')) ?? ProductSort::Popular,
            'price' => PriceRange::tryFrom((string) ($validated['price'] ?? '')),
            'minPrice' => $validated['min_price'] ?? null,
            'maxPrice' => $validated['max_price'] ?? null,
            'rating' => $validated['rating'] ?? null,
            'page' => $validated['page'],
            'perPage' => $validated['per_page'],
        ];
    }

    private function wholeNumber(string $key): ?int
    {
        $value = $this->query($key);

        if (! is_string($value) || ! ctype_digit($value)) {
            return null;
        }

        return (int) min((int) $value, 10_000_000);
    }

    private function cleanString(string $key, int $maxLength): ?string
    {
        $value = $this->query($key);

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : mb_substr($value, 0, $maxLength);
    }
}
