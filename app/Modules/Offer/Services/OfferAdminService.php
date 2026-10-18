<?php

namespace App\Modules\Offer\Services;

use App\Modules\Offer\Models\OfferCampaign;
use App\Modules\Offer\Models\OfferPageSetting;
use App\Modules\Product\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class OfferAdminService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function list(): Collection
    {
        return OfferCampaign::query()
            ->with('products')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(fn (OfferCampaign $campaign): array => $campaign->toAdminArray());
    }

    public function findOrFail(int $id): OfferCampaign
    {
        $campaign = OfferCampaign::query()->with('products')->find($id);

        if ($campaign === null) {
            abort(404);
        }

        return $campaign;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): OfferCampaign
    {
        $productIds = $this->productIds($data);
        $campaign = OfferCampaign::query()->create($this->attributes($data));
        $this->syncProducts($campaign, $productIds);

        return $campaign->load('products');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(OfferCampaign $campaign, array $data): OfferCampaign
    {
        $campaign->update($this->attributes($data));
        $this->syncProducts($campaign, $this->productIds($data));

        return $campaign->refresh()->load('products');
    }

    public function delete(OfferCampaign $campaign): void
    {
        $campaign->delete();
    }

    /**
     * @return array<string, mixed>
     */
    public function pageSetting(): array
    {
        return OfferPageSetting::current()->toAdminArray();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updatePageSetting(array $data): OfferPageSetting
    {
        $setting = OfferPageSetting::current();
        $setting->update([
            'section_title' => $data['section_title'],
            'section_subtitle' => filled($data['section_subtitle'] ?? null) ? $data['section_subtitle'] : null,
            'eyebrow' => $data['eyebrow'],
            'theme' => $data['theme'],
            'countdown_enabled' => (bool) ($data['countdown_enabled'] ?? false),
            'countdown_ends_at' => filled($data['countdown_ends_at'] ?? null) ? $data['countdown_ends_at'] : null,
            'is_enabled' => (bool) ($data['is_enabled'] ?? false),
        ]);

        return $setting->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function formOptions(): array
    {
        return [
            'products' => Product::query()
                ->where('is_unlaunched', false)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Product $product): array => [
                    'id' => $product->id,
                    'name' => $product->name,
                ])
                ->values()
                ->all(),
            'productLimit' => OfferCampaign::ProductLimit,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        $startsAt = filled($data['starts_at'] ?? null) ? $data['starts_at'] : null;
        $endsAt = filled($data['ends_at'] ?? null) ? $data['ends_at'] : null;

        if ($startsAt !== null && $endsAt !== null && strtotime((string) $endsAt) <= strtotime((string) $startsAt)) {
            throw ValidationException::withMessages([
                'ends_at' => 'End must be after the start.',
            ]);
        }

        return [
            'title' => $data['title'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'countdown_enabled' => (bool) ($data['countdown_enabled'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<int>
     */
    private function productIds(array $data): array
    {
        $ids = collect($data['product_ids'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        if ($ids->count() > OfferCampaign::ProductLimit) {
            throw ValidationException::withMessages([
                'product_ids' => 'Choose up to '.OfferCampaign::ProductLimit.' products for this card.',
            ]);
        }

        $existing = Product::query()->whereIn('id', $ids)->pluck('id')->map(fn ($id): int => (int) $id);

        if ($existing->count() !== $ids->count()) {
            throw ValidationException::withMessages([
                'product_ids' => 'One or more products could not be found.',
            ]);
        }

        return $ids->all();
    }

    /**
     * @param  list<int>  $productIds
     */
    private function syncProducts(OfferCampaign $campaign, array $productIds): void
    {
        $sync = [];

        foreach (array_values($productIds) as $index => $productId) {
            $sync[$productId] = ['sort_order' => $index];
        }

        $campaign->products()->sync($sync);
    }
}
