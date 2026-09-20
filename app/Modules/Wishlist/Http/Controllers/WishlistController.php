<?php

namespace App\Modules\Wishlist\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Wishlist\Http\Requests\StoreWishlistItemRequest;
use App\Modules\Wishlist\Services\WishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(private readonly WishlistService $wishlist) {}

    public function index(): RedirectResponse
    {
        return redirect()
            ->route('home')
            ->with('open_sheet', 'wishlist');
    }

    public function store(StoreWishlistItemRequest $request): RedirectResponse
    {
        $added = $this->wishlist->toggle($request, (int) $request->validated('product_id'));

        return back()
            ->with('success', $added ? 'Added to wishlist.' : 'Removed from wishlist.')
            ->with('open_sheet', $added ? 'wishlist' : null);
    }

    public function destroy(Request $request, int $item): RedirectResponse
    {
        $this->wishlist->remove($request, $item);

        return back()->with('success', 'Removed from wishlist.')->with('open_sheet', 'wishlist');
    }
}
