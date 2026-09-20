<?php

namespace App\Modules\Banner\Services;

use App\Modules\Banner\Enums\BannerPosition;
use App\Modules\Banner\Models\Banner;
use App\Modules\Banner\Repositories\Contracts\BannerRepositoryInterface;

class BannerStorefrontService
{
    public function __construct(
        private readonly BannerRepositoryInterface $banners,
    ) {}

    /**
     * @return array{
     *     heroBanners: list<array<string, mixed>>,
     *     landscapeBanners: list<array<string, mixed>>,
     *     footerBanners: list<array<string, mixed>>
     * }
     */
    public function forHome(): array
    {
        return [
            'heroBanners' => $this->forPosition(BannerPosition::Hero),
            'landscapeBanners' => $this->forPosition(BannerPosition::Middle),
            'footerBanners' => $this->forPosition(BannerPosition::Footer),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forOfferZone(): array
    {
        return $this->banners
            ->activeByPosition(BannerPosition::OfferZone)
            ->map(fn (Banner $banner): array => $banner->toStorefrontArray())
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forPosition(BannerPosition $position): array
    {
        return $this->banners
            ->activeByPosition($position)
            ->map(fn (Banner $banner): array => $banner->toStorefrontArray())
            ->filter(fn (array $banner): bool => filled($banner['web_image']) || filled($banner['mobile_image']))
            ->values()
            ->all();
    }
}
