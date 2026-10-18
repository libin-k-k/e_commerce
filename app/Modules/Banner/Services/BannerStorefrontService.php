<?php

namespace App\Modules\Banner\Services;

use App\Modules\Banner\Enums\BannerPosition;
use App\Modules\Banner\Models\Banner;
use App\Modules\Banner\Models\BannerSection;
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
     *     footerBanners: list<array<string, mixed>>,
     *     dealsBanner: array<string, mixed>|null
     * }
     */
    public function forHome(): array
    {
        return [
            'heroBanners' => $this->forPosition(BannerPosition::Hero),
            'landscapeBanners' => $this->forPosition(BannerPosition::Middle),
            'footerBanners' => $this->forPosition(BannerPosition::Footer),
            'dealsBanner' => $this->forPosition(BannerPosition::HomeDeals)[0] ?? null,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forOfferZone(): array
    {
        return $this->forPosition(BannerPosition::OfferZone);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function forProductListing(): ?array
    {
        return $this->forPosition(BannerPosition::ProductListing)[0] ?? null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forPosition(BannerPosition $position): array
    {
        $section = BannerSection::query()->where('position', $position->value)->first();

        if ($section !== null && ! $section->is_enabled) {
            return [];
        }

        $showCount = (bool) ($section?->show_count ?? false);

        return $this->banners
            ->activeByPosition($position)
            ->map(function (Banner $banner) use ($showCount): array {
                return [
                    ...$banner->toStorefrontArray(),
                    'showCount' => $showCount,
                ];
            })
            ->filter(fn (array $banner): bool => filled($banner['web_image']) || filled($banner['mobile_image']))
            ->values()
            ->all();
    }
}
