<?php

namespace App\Modules\Banner\Services;

use App\Modules\Banner\Enums\BannerNavigationLink;
use App\Modules\Banner\Enums\BannerPosition;
use App\Modules\Banner\Enums\BannerStyle;
use App\Modules\Banner\Models\Banner;
use App\Modules\Banner\Models\BannerSection;
use App\Modules\Banner\Repositories\Contracts\BannerRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listForPosition(BannerPosition $position): Collection
    {
        return $this->banners->byPosition($position)->map(
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
            $data['web_image_path'] = $this->storeImage($webImage, 'banners/web', 1920);
        }

        if ($mobileImage !== null) {
            $data['mobile_image_path'] = $this->storeImage($mobileImage, 'banners/mobile', 1080);
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
            $data['web_image_path'] = $this->storeImage($webImage, 'banners/web', 1920);
        }

        if ($mobileImage !== null) {
            $this->deleteStoredImage($banner->mobile_image_path);
            $data['mobile_image_path'] = $this->storeImage($mobileImage, 'banners/mobile', 1080);
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
     * @param  list<int>  $ids
     */
    public function reorder(array $ids): void
    {
        $this->banners->reorder($ids);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function sections(): Collection
    {
        return BannerSection::query()
            ->orderBy('id')
            ->get()
            ->map(fn (BannerSection $section): array => $section->toAdminArray());
    }

    /**
     * @param  list<array{position: string, is_enabled: bool, show_count: bool}>  $sections
     */
    public function updateSections(array $sections): void
    {
        foreach ($sections as $section) {
            BannerSection::query()
                ->where('position', $section['position'])
                ->update([
                    'is_enabled' => (bool) $section['is_enabled'],
                    'show_count' => (bool) $section['show_count'],
                ]);
        }
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
                'web' => 'Website banner · JPG, PNG, WebP, or animated GIF',
                'mobile' => 'Mobile banner · JPG, PNG, WebP, or animated GIF',
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
        $data['display_width'] = filled($data['display_width'] ?? null) ? (int) $data['display_width'] : null;
        $data['display_height'] = filled($data['display_height'] ?? null) ? (int) $data['display_height'] : null;
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

    private function storeImage(UploadedFile $file, string $directory, int $maxWidth): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = (string) $file->getMimeType();

        if ($extension === 'gif' || $mime === 'image/gif') {
            $path = trim($directory, '/').'/'.Str::uuid()->toString().'.gif';
            Storage::disk('public')->put($path, $file->getContent());

            return $path;
        }

        return $this->webpImageConverter->store($file, $directory, $maxWidth);
    }

    private function deleteStoredImage(?string $path): void
    {
        if ($path === null || $path === '' || str_starts_with($path, 'assets/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
