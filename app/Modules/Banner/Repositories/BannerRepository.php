<?php

namespace App\Modules\Banner\Repositories;

use App\Modules\Banner\Enums\BannerPosition;
use App\Modules\Banner\Models\Banner;
use App\Modules\Banner\Repositories\Contracts\BannerRepositoryInterface;
use Illuminate\Support\Collection;

class BannerRepository implements BannerRepositoryInterface
{
    public function allOrdered(): Collection
    {
        return Banner::query()
            ->orderBy('position')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
    }

    public function find(int $id): ?Banner
    {
        return Banner::query()->find($id);
    }

    public function create(array $data): Banner
    {
        return Banner::query()->create($data);
    }

    public function update(Banner $banner, array $data): Banner
    {
        $banner->update($data);

        return $banner->refresh();
    }

    public function delete(Banner $banner): void
    {
        $banner->delete();
    }

    public function reorder(array $ids): void
    {
        foreach (array_values($ids) as $index => $id) {
            Banner::query()->whereKey($id)->update(['sort_order' => $index]);
        }
    }

    public function activeByPosition(BannerPosition $position): Collection
    {
        return Banner::query()
            ->where('position', $position)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
    }

    public function byPosition(BannerPosition $position): Collection
    {
        return Banner::query()
            ->where('position', $position)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
    }
}
