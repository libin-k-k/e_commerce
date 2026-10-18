<?php

namespace App\Modules\Coupon\Http\Controllers\Admin;

use App\Core\Seo\SeoJsonLoader;
use App\Http\Controllers\Controller;
use App\Modules\Coupon\Http\Requests\Admin\StoreCouponRequest;
use App\Modules\Coupon\Http\Requests\Admin\UpdateCouponRequest;
use App\Modules\Coupon\Services\CouponAdminService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    public function __construct(
        private readonly CouponAdminService $coupons,
        private readonly SeoJsonLoader $seoJsonLoader,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Pages/Coupons/Index', [
            'seo' => $this->seoJsonLoader->load('Modules/Admin/Coupons/index'),
            'coupons' => $this->coupons->list(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Pages/Coupons/Create', [
            'seo' => $this->seoJsonLoader->load('Modules/Admin/Coupons/create'),
            'options' => $this->coupons->formOptions(),
        ]);
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $this->coupons->create($request->validated());

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Coupon created.');
    }

    public function edit(int $coupon): Response
    {
        $model = $this->coupons->findOrFail($coupon);

        return Inertia::render('Admin/Pages/Coupons/Edit', [
            'seo' => $this->seoJsonLoader->load('Modules/Admin/Coupons/edit'),
            'coupon' => $model->toAdminArray(),
            'options' => $this->coupons->formOptions(),
        ]);
    }

    public function update(UpdateCouponRequest $request, int $coupon): RedirectResponse
    {
        $model = $this->coupons->findOrFail($coupon);
        $this->coupons->update($model, $request->validated());

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Coupon updated.');
    }

    public function destroy(int $coupon): RedirectResponse
    {
        $model = $this->coupons->findOrFail($coupon);
        $this->coupons->delete($model);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Coupon deleted.');
    }
}
