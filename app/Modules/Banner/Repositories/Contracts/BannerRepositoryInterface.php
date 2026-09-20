<?php

namespace App\Modules\Banner\Repositories\Contracts;

use App\Modules\Banner\Enums\BannerPosition;
use App\Modules\Banner\Models\Banner;
use Illuminate\Support\Collection;

interface BannerRepositoryInterface
{
    /**
     * @return Collection<int, Banner>
     */
    public function allOrdered(): Collection;

    public function find(int $id): ?Banner;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Banner;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Banner $banner, array $data): Banner;

    public function delete(Banner $banner): void;

    /**
     * @return Collection<int, Banner>
     */
    public function activeByPosition(BannerPosition $position): Collection;
}
