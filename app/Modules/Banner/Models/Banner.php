<?php

namespace App\Modules\Banner\Models;

use App\Core\Media\PublicUrl;
use App\Modules\Banner\Enums\BannerNavigationLink;
use App\Modules\Banner\Enums\BannerPosition;
use App\Modules\Banner\Enums\BannerStyle;
use Database\Factories\BannerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    /** @use HasFactory<BannerFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'position',
        'style',
        'web_image_path',
        'mobile_image_path',
        'navigation_link',
        'custom_url',
        'category_slug',
        'button_text',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => BannerPosition::class,
            'style' => BannerStyle::class,
            'navigation_link' => BannerNavigationLink::class,
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): BannerFactory
    {
        return BannerFactory::new();
    }

    public function webImageUrl(): ?string
    {
        return $this->resolveImageUrl($this->web_image_path);
    }

    public function mobileImageUrl(): ?string
    {
        return $this->resolveImageUrl($this->mobile_image_path);
    }

    public function resolvedHref(): ?string
    {
        return $this->navigation_link->href($this->custom_url, $this->category_slug);
    }

    /**
     * @return array<string, mixed>
     */
    public function toAdminArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'position' => $this->position->value,
            'position_label' => $this->position->label(),
            'style' => $this->style->value,
            'style_label' => $this->style->label(),
            'web_image_path' => $this->web_image_path,
            'web_image_url' => $this->webImageUrl(),
            'mobile_image_path' => $this->mobile_image_path,
            'mobile_image_url' => $this->mobileImageUrl(),
            'navigation_link' => $this->navigation_link->value,
            'navigation_link_label' => $this->navigation_link->label(),
            'custom_url' => $this->custom_url,
            'category_slug' => $this->category_slug,
            'button_text' => $this->button_text,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'href' => $this->resolvedHref(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toStorefrontArray(): array
    {
        $webImage = $this->webImageUrl();
        $mobileImage = $this->mobileImageUrl();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'text' => $this->subtitle,
            'cta' => $this->button_text ?: 'Shop now',
            'href' => $this->resolvedHref() ?? '/products',
            'style' => $this->style->value,
            'image' => $webImage,
            'web_image' => $webImage,
            'mobile_image' => $mobileImage,
        ];
    }

    private function resolveImageUrl(?string $path): ?string
    {
        return PublicUrl::for($path);
    }
}
