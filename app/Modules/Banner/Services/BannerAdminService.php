<?php

namespace App\Modules\Banner\Services;

use App\Modules\Banner\Enums\BannerNavigationLink;
use App\Modules\Banner\Enums\BannerPosition;
use App\Modules\Banner\Enums\BannerStyle;
use App\Modules\Banner\Models\Banner;
use App\Modules\Banner\Repositories\Contracts\BannerRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class BannerAdminService
{
    public function __construct(
        private readonly BannerRepositoryInterface $banners,
        private readonly WebpImageConverter $webpImageConverter,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function list(): Collection
    {
        return $this->banners->allOrdered()->map(
            fn (Banner $banner): array => $banner->toAdminArray(),
        );
    }

    public function findOrFail(int $id): Banner
    {
        $banner = $this->banners->find($id);

        if ($banner === null) {
            abort(404);
        }

        return $banner;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $webImage = null, ?UploadedFile $mobileImage = null): Banner
    {
        if ($webImage !== null) {
            $data['web_image_path'] = $this->webpImageConverter->store($webImage, 'banners/web', 1920);
        }

        if ($mobileImage !== null) {
            $data['mobile_image_path'] = $this->webpImageConverter->store($mobileImage, 'banners/mobile', 1080);
        }

        return $this->banners->create($this->normalize($data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Banner $banner, array $data, ?UploadedFile $webImage = null, ?UploadedFile $mobileImage = null): Banner
    {
        if ($webImage !== null) {
            $this->deleteStoredImage($banner->web_image_path);
            $data['web_image_path'] = $this->webpImageConverter->store($webImage, 'banners/web', 1920);
        }

        if ($mobileImage !== null) {
            $this->deleteStoredImage($banner->mobile_image_path);
            $data['mobile_image_path'] = $this->webpImageConverter->store($mobileImage, 'banners/mobile', 1080);
        }

        return $this->banners->update($banner, $this->normalize($data));
    }

    public function delete(Banner $banner): void
    {
        $this->deleteStoredImage($banner->web_image_path);
        $this->deleteStoredImage($banner->mobile_image_path);
        $this->banners->delete($banner);
    }

    /**
     * @return array<string, mixed>
     */
    public function formOptions(): array
    {
        return [
            'positions' => BannerPosition::options(),
            'styles' => BannerStyle::options(),
            'navigationLinks' => BannerNavigationLink::options(),
            'buttonPresets' => [
                'Shop Now',
                'View All',
                'Explore',
                'Get Offer',
                'Learn More',
                'Buy Now',
            ],
            'imageHints' => [
                'web' => 'Website banner · 21:9 · shown on desktop/tablet web',
                'mobile' => 'Mobile app (APK) banner · 4:5 · not used on website',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        $link = BannerNavigationLink::from($data['navigation_link']);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['subtitle'] = ($data['subtitle'] ?? null) ?: null;
        $data['button_text'] = ($data['button_text'] ?? null) ?: null;
        $data['custom_url'] = $link === BannerNavigationLink::Custom
            ? (($data['custom_url'] ?? null) ?: null)
            : null;
        $data['category_slug'] = $link === BannerNavigationLink::Category
            ? (($data['category_slug'] ?? null) ?: null)
            : null;

        unset($data['web_image'], $data['mobile_image'], $data['image']);

        return $data;
    }

    private function deleteStoredImage(?string $path): void
    {
        if ($path === null || $path === '' || str_starts_with($path, 'assets/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
