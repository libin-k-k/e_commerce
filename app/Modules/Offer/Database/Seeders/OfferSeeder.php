<?php

namespace App\Modules\Offer\Database\Seeders;

use App\Modules\Offer\Models\OfferCampaign;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        $deals = [
            'wireless-headphones',
            'soft-cotton-tee',
            'weekend-denim',
            'day-dress',
        ];
        $rated = [
            'everyday-tote-bag',
            'compact-wallet',
            'canvas-backpack',
            'travel-pouch',
        ];

        $this->campaign('Deals for you', $deals, 1, true);
        $this->campaign('4 stars', $rated, 2, false);
    }

    /**
     * @param  list<string>  $slugs
     */
    private function campaign(string $title, array $slugs, int $sortOrder, bool $countdown): void
    {
        $products = Product::query()->whereIn('slug', $slugs)->get()->keyBy('slug');

        if ($products->count() < count($slugs)) {
            return;
        }

        $campaign = OfferCampaign::query()->updateOrCreate(
            ['title' => $title],
            [
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(3),
                'countdown_enabled' => $countdown,
                'is_active' => true,
                'sort_order' => $sortOrder,
            ],
        );

        $sync = [];

        foreach (array_values($slugs) as $index => $slug) {
            $sync[$products[$slug]->id] = ['sort_order' => $index];
        }

        $campaign->products()->sync($sync);
    }
}
