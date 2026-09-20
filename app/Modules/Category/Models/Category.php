<?php

namespace App\Modules\Category\Models;

use App\Core\Media\PublicUrl;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image_path',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function isMain(): bool
    {
        return $this->parent_id === null;
    }

    public function imageUrl(): ?string
    {
        return $this->resolveImageUrl($this->image_path);
    }

    /**
     * @return array<string, mixed>
     */
    public function toAdminArray(bool $withChildren = false): array
    {
        $data = [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'parent_name' => $this->parent?->name,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_path' => $this->image_path,
            'image_url' => $this->imageUrl(),
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'is_main' => $this->isMain(),
            'children_count' => $this->relationLoaded('children')
                ? $this->children->count()
                : ($this->children_count ?? null),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];

        if ($withChildren && $this->relationLoaded('children')) {
            $data['children'] = $this->children
                ->map(fn (Category $child): array => $child->toAdminArray())
                ->values()
                ->all();
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function toStorefrontArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->imageUrl(),
            'href' => '/products?category='.$this->slug,
        ];
    }

    private function resolveImageUrl(?string $path): ?string
    {
        return PublicUrl::for($path);
    }
}
