<?php

namespace App\Modules\Offer\Http\Controllers\Admin;

use App\Core\Seo\SeoJsonLoader;
use App\Http\Controllers\Controller;
use App\Modules\Banner\Enums\BannerPosition;
use App\Modules\Banner\Services\BannerAdminService;
use App\Modules\Offer\Enums\OfferTheme;
use App\Modules\Offer\Http\Requests\Admin\StoreOfferCampaignRequest;
use App\Modules\Offer\Http\Requests\Admin\UpdateOfferCampaignRequest;
use App\Modules\Offer\Http\Requests\Admin\UpdateOfferPageSettingRequest;
use App\Modules\Offer\Services\OfferAdminService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OfferCampaignController extends Controller
{
    public function __construct(
        private readonly OfferAdminService $offers,
        private readonly BannerAdminService $banners,
        private readonly SeoJsonLoader $seoJsonLoader,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Pages/Offers/Index', [
            'seo' => $this->seoJsonLoader->load('Modules/Admin/Offers/index'),
            'offers' => $this->offers->list(),
            'pageSetting' => $this->offers->pageSetting(),
            'themes' => OfferTheme::options(),
            'offerBanners' => $this->banners->listForPosition(BannerPosition::OfferZone),
        ]);
    }

    public function updateSettings(UpdateOfferPageSettingRequest $request): RedirectResponse
    {
        $this->offers->updatePageSetting($request->validated());

        return redirect()
            ->route('admin.offers.index')
            ->with('success', 'Offer page updated.');
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Pages/Offers/Create', [
            'seo' => $this->seoJsonLoader->load('Modules/Admin/Offers/create'),
            'options' => $this->offers->formOptions(),
        ]);
    }

    public function store(StoreOfferCampaignRequest $request): RedirectResponse
    {
        $this->offers->create($request->validated());

        return redirect()
            ->route('admin.offers.index')
            ->with('success', 'Offer saved.');
    }

    public function edit(int $offer): Response
    {
        $campaign = $this->offers->findOrFail($offer);

        return Inertia::render('Admin/Pages/Offers/Edit', [
            'seo' => $this->seoJsonLoader->load('Modules/Admin/Offers/edit'),
            'offer' => $campaign->toAdminArray(),
            'options' => $this->offers->formOptions(),
        ]);
    }

    public function update(UpdateOfferCampaignRequest $request, int $offer): RedirectResponse
    {
        $campaign = $this->offers->findOrFail($offer);
        $this->offers->update($campaign, $request->validated());

        return redirect()
            ->route('admin.offers.index')
            ->with('success', 'Offer updated.');
    }

    public function destroy(int $offer): RedirectResponse
    {
        $campaign = $this->offers->findOrFail($offer);
        $this->offers->delete($campaign);

        return redirect()
            ->route('admin.offers.index')
            ->with('success', 'Offer deleted.');
    }
}
