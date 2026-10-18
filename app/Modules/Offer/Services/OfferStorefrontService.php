<?php

namespace App\Modules\Offer\Services;

use App\Modules\Offer\Models\OfferCampaign;
use App\Modules\Offer\Models\OfferPageSetting;
use Illuminate\Support\Collection;

class OfferStorefrontService
{
    public function hasCampaigns(): bool
    {
        return OfferCampaign::query()->exists();
    }

    /**
     * Offer Zone title, countdown, and seasonal theme.
     *
     * @return array<string, mixed>
     */
    public function presentation(): array
    {
        return OfferPageSetting::current()->toStorefrontArray();
    }

    /**
     * Products and countdown for the offers page when campaigns control it.
     *
     * @return array{products: list<array<string, mixed>>, schedule: array{countdownEnabled: bool, endsAt: ?string, startsAt: ?string}}
     */
    public function page(): array
    {
        $campaigns = $this->liveCampaigns();
        $seen = [];
        $products = [];

        foreach ($campaigns as $campaign) {
            foreach ($campaign->products as $product) {
                if (isset($seen[$product->id]) || $product->is_unlaunched) {
                    continue;
                }

                $seen[$product->id] = true;
                $products[] = $product->toStorefrontArray();
            }
        }

        $countdown = $campaigns
            ->filter(fn (OfferCampaign $campaign): bool => $campaign->countdown_enabled && $campaign->ends_at !== null)
            ->sortBy(fn (OfferCampaign $campaign) => $campaign->ends_at)
            ->first();

        return [
            'products' => $products,
            'schedule' => [
                'countdownEnabled' => $countdown !== null,
                'endsAt' => $countdown?->ends_at?->toIso8601String(),
                'startsAt' => $countdown?->starts_at?->toIso8601String(),
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $categories
     * @return list<array<string, mixed>>
     */
    public function cardsFromCategories(array $categories): array
    {
        $cards = [];

        foreach ($categories as $category) {
            $products = array_slice($category['products'] ?? [], 0, OfferCampaign::ProductLimit);

            if ($products === []) {
                continue;
            }

            $cards[] = [
                'id' => $category['slug'],
                'title' => $category['name'],
                'href' => $category['href'],
                'countdownEnabled' => false,
                'endsAt' => null,
                'products' => array_map(fn (array $product): array => $this->categoryTile($product), $products),
            ];
        }

        return $cards;
    }

    /**
     * @return Collection<int, OfferCampaign>
     */
    private function liveCampaigns(): Collection
    {
        return OfferCampaign::query()
            ->live()
            ->with(['products' => fn ($query) => $query->where('is_unlaunched', false)])
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    private function categoryTile(array $product): array
    {
        $discount = (int) ($product['discountPercent'] ?? 0);

        return [
            'slug' => $product['slug'],
            'name' => $product['name'],
            'image' => $product['image'] ?? null,
            'href' => '/products/'.$product['slug'],
            'discountPercent' => $discount > 0 ? $discount : null,
            'label' => (string) $product['name'],
        ];
    }
}
