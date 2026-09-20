<?php

namespace App\Http\Middleware;

use App\Modules\Cart\Services\CartService;
use App\Modules\Catalog\Services\CategoryMenuService;
use App\Modules\Wishlist\Services\WishlistService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'website.app';

    /**
     * Determine the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appName' => config('app.name'),
            'cartCount' => fn () => app(CartService::class)->count($request),
            'wishlistCount' => fn () => app(WishlistService::class)->count($request),
            'wishlistProductIds' => fn () => app(WishlistService::class)->productIds($request),
            'cart' => function () use ($request) {
                $items = app(CartService::class)->items($request);
                $subtotal = $items->sum('line_total');

                return [
                    'items' => $items,
                    'subtotal' => $subtotal,
                    'subtotalLabel' => '₹'.number_format((float) $subtotal, 2),
                ];
            },
            'wishlist' => fn () => [
                'items' => app(WishlistService::class)->items($request),
            ],
            'categoryMenu' => fn () => app(CategoryMenuService::class)->menu(),
            'auth' => [
                'user' => $request->user()
                    ? [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                        'mobile' => $request->user()->mobile,
                        'is_admin' => (bool) $request->user()->is_admin,
                    ]
                    : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'open_sheet' => fn () => $request->session()->get('open_sheet'),
            ],
        ];
    }
}
