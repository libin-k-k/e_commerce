<?php

namespace App\Modules\Product\Repositories;

use App\Core\Media\PublicUrl;
use App\Modules\Product\Repositories\Contracts\ProductRepositoryInterface;

class InMemoryProductRepository implements ProductRepositoryInterface
{
    public function all(): array
    {
        return $this->catalog();
    }

    public function findBySlug(string $slug): ?array
    {
        foreach ($this->catalog() as $product) {
            if ($product['slug'] === $slug) {
                return $product;
            }
        }

        return null;
    }

    public function featured(int $limit = 4): array
    {
        return array_slice($this->catalog(), 0, $limit);
    }

    public function onSale(): array
    {
        return array_values(array_filter(
            $this->catalog(),
            fn (array $product): bool => filled($product['compareAtPrice'] ?? null),
        ));
    }

    public function related(string $slug, int $limit = 4): array
    {
        $related = array_values(array_filter(
            $this->catalog(),
            fn (array $product): bool => $product['slug'] !== $slug,
        ));

        return array_slice($related, 0, $limit);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function catalog(): array
    {
        return [
            [
                'slug' => 'wireless-headphones',
                'name' => 'Wireless headphones',
                'price' => '₹79.00',
                'priceValue' => 79.00,
                'compareAtPrice' => '₹99.00',
                'badge' => '20% off',
                'stock' => 24,
                'inStock' => true,
                'rating' => 4.6,
                'reviewCount' => 128,
                'shortDescription' => 'Clear sound, all-day comfort, and wireless freedom for work and travel.',
                'description' => 'Enjoy rich audio with deep bass and crisp highs. Soft ear cushions and a lightweight frame make long sessions comfortable. Pair instantly over Bluetooth and switch devices with ease.',
                'specs' => [
                    ['label' => 'Connectivity', 'value' => 'Bluetooth 5.3'],
                    ['label' => 'Battery', 'value' => 'Up to 30 hours'],
                    ['label' => 'Charging', 'value' => 'USB-C fast charge'],
                    ['label' => 'Weight', 'value' => '245 g'],
                ],
                'colors' => ['Black', 'Ivory', 'Blue'],
                'sizes' => [],
                'image' => $this->productImage('wireless_headphone_1.png'),
                'images' => [
                    $this->productImage('wireless_headphone_1.png'),
                    $this->productImage('wireless_headphone_2.png'),
                    $this->productImage('wireless_headphone_3.png'),
                ],
                'meta_title' => 'Wireless headphones',
                'meta_description' => 'Shop wireless headphones with long battery life, Bluetooth 5.3, and all-day comfort.',
                'meta_keywords' => ['wireless headphones', 'bluetooth headphones', 'audio'],
                'is_indexable' => true,
            ],
            [
                'slug' => 'minimal-desk-lamp',
                'name' => 'Minimal desk lamp',
                'price' => '₹45.00',
                'priceValue' => 45.00,
                'compareAtPrice' => null,
                'badge' => null,
                'stock' => 40,
                'inStock' => true,
                'rating' => 4.4,
                'reviewCount' => 64,
                'shortDescription' => 'A clean, adjustable lamp that lights your desk without clutter.',
                'description' => 'Soft ambient light with adjustable brightness for reading, work, and evening wind-down. The minimal base fits small desks and shelves. Warm and cool light modes included.',
                'specs' => [
                    ['label' => 'Light modes', 'value' => 'Warm / Cool / Mix'],
                    ['label' => 'Power', 'value' => 'USB / plug adapter'],
                    ['label' => 'Material', 'value' => 'Metal + ABS'],
                    ['label' => 'Height', 'value' => '42 cm'],
                ],
                'colors' => ['White', 'Matte Black'],
                'sizes' => [],
                'image' => $this->productImage('minimal_desk_lamp_1.png'),
                'images' => [
                    $this->productImage('minimal_desk_lamp_1.png'),
                    $this->productImage('minimal_desk_lamp_2.png'),
                    $this->productImage('minimal_desk_lamp_3.png'),
                ],
                'meta_title' => 'Minimal desk lamp',
                'meta_description' => 'Buy a minimal desk lamp with adjustable brightness and a compact footprint.',
                'meta_keywords' => ['desk lamp', 'home office', 'lighting'],
                'is_indexable' => true,
            ],
            [
                'slug' => 'everyday-tote-bag',
                'name' => 'Everyday tote bag',
                'price' => '₹36.00',
                'priceValue' => 36.00,
                'compareAtPrice' => '₹48.00',
                'badge' => 'Sale',
                'stock' => 18,
                'inStock' => true,
                'rating' => 4.2,
                'reviewCount' => 41,
                'shortDescription' => 'Roomy tote for daily carry, shopping, and weekend trips.',
                'description' => 'Durable fabric with reinforced handles and an interior pocket for essentials. Spacious enough for a laptop sleeve, water bottle, and daily kit without looking bulky.',
                'specs' => [
                    ['label' => 'Material', 'value' => 'Canvas'],
                    ['label' => 'Capacity', 'value' => '18 L'],
                    ['label' => 'Care', 'value' => 'Spot clean'],
                    ['label' => 'Closure', 'value' => 'Magnetic snap'],
                ],
                'colors' => ['Sand', 'Olive', 'Black'],
                'sizes' => ['One size'],
                'image' => $this->productImage('everyday_tote_bag_1.png'),
                'images' => [
                    $this->productImage('everyday_tote_bag_1.png'),
                    $this->productImage('everyday_tote_bag_2.png'),
                    $this->productImage('everyday_tote_bag_3.png'),
                ],
                'meta_title' => 'Everyday tote bag',
                'meta_description' => 'Shop a durable everyday tote bag with reinforced handles and roomy storage.',
                'meta_keywords' => ['tote bag', 'bags', 'accessories'],
                'is_indexable' => true,
            ],
            [
                'slug' => 'ceramic-mug-set',
                'name' => 'Ceramic mug set',
                'price' => '₹28.00',
                'priceValue' => 28.00,
                'compareAtPrice' => null,
                'badge' => null,
                'stock' => 55,
                'inStock' => true,
                'rating' => 4.7,
                'reviewCount' => 89,
                'shortDescription' => 'A four-piece ceramic mug set for coffee, tea, and cozy mornings.',
                'description' => 'Smooth glaze, comfortable handles, and microwave-safe ceramic. Stackable design saves cupboard space while looking great on open shelves.',
                'specs' => [
                    ['label' => 'Pieces', 'value' => 'Set of 4'],
                    ['label' => 'Capacity', 'value' => '350 ml each'],
                    ['label' => 'Microwave', 'value' => 'Safe'],
                    ['label' => 'Dishwasher', 'value' => 'Safe'],
                ],
                'colors' => ['Cream', 'Stone'],
                'sizes' => [],
                'image' => $this->productImage('ceramic_mug_set_1.png'),
                'images' => [
                    $this->productImage('ceramic_mug_set_1.png'),
                    $this->productImage('ceramic_mug_set_2.png'),
                    $this->productImage('ceramic_mug_set_3.png'),
                ],
                'meta_title' => 'Ceramic mug set',
                'meta_description' => 'Buy a ceramic mug set of four - microwave and dishwasher safe.',
                'meta_keywords' => ['ceramic mugs', 'home', 'kitchen'],
                'is_indexable' => true,
            ],
        ];
    }

    private function productImage(string $filename): string
    {
        return (string) PublicUrl::for('assets/website/products/'.$filename);
    }
}
