<?php

namespace App\Modules\Banner\Http\Controllers\Admin;

use App\Core\Seo\SeoJsonLoader;
use App\Http\Controllers\Controller;
use App\Modules\Banner\Http\Requests\Admin\StoreBannerRequest;
use App\Modules\Banner\Http\Requests\Admin\UpdateBannerRequest;
use App\Modules\Banner\Services\BannerAdminService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BannerController extends Controller
{
    public function __construct(
        private readonly BannerAdminService $bannerAdminService,
        private readonly SeoJsonLoader $seoJsonLoader,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Pages/Banners/Index', [
            'seo' => $this->seoJsonLoader->load('Modules/Admin/Banners/index'),
            'banners' => $this->bannerAdminService->list(),
            'options' => $this->bannerAdminService->formOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Pages/Banners/Create', [
            'seo' => $this->seoJsonLoader->load('Modules/Admin/Banners/create'),
            'options' => $this->bannerAdminService->formOptions(),
        ]);
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $this->bannerAdminService->create(
            $request->safe()->except(['web_image', 'mobile_image']),
            $request->file('web_image'),
            $request->file('mobile_image'),
        );

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner created.');
    }

    public function edit(int $banner): Response
    {
        $model = $this->bannerAdminService->findOrFail($banner);

        return Inertia::render('Admin/Pages/Banners/Edit', [
            'seo' => $this->seoJsonLoader->load('Modules/Admin/Banners/edit'),
            'banner' => $model->toAdminArray(),
            'options' => $this->bannerAdminService->formOptions(),
        ]);
    }

    public function update(UpdateBannerRequest $request, int $banner): RedirectResponse
    {
        $model = $this->bannerAdminService->findOrFail($banner);

        $this->bannerAdminService->update(
            $model,
            $request->safe()->except(['web_image', 'mobile_image']),
            $request->file('web_image'),
            $request->file('mobile_image'),
        );

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner updated.');
    }

    public function destroy(int $banner): RedirectResponse
    {
        $model = $this->bannerAdminService->findOrFail($banner);
        $this->bannerAdminService->delete($model);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner deleted.');
    }
}
