<?php

namespace App\Modules\Banner\Enums;

enum BannerNavigationLink: string
{
    case None = 'none';
    case Home = 'home';
    case Products = 'products';
    case Sale = 'sale';
    case Featured = 'featured';
    case NewArrivals = 'new_arrivals';
    case Category = 'category';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::None => 'No Link',
            self::Home => 'Home',
            self::Products => 'All Products',
            self::Sale => 'Offer Zone',
            self::Featured => 'Featured',
            self::NewArrivals => 'New Arrivals',
            self::Category => 'Category',
            self::Custom => 'Custom URL',
        };
    }

    public function href(?string $customUrl = null, ?string $categorySlug = null): ?string
    {
        return match ($this) {
            self::None => null,
            self::Home => '/',
            self::Products => '/products',
            self::Sale => '/offers',
            self::Featured => '/products?filter=featured',
            self::NewArrivals => '/products?filter=new',
            self::Category => $categorySlug ? '/products?category='.$categorySlug : '/products',
            self::Custom => $customUrl,
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases(),
        );
    }
}
