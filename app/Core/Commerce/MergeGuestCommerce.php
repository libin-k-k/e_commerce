<?php

namespace App\Core\Commerce;

use App\Models\User;
use App\Modules\Cart\Services\CartService;
use App\Modules\Wishlist\Services\WishlistService;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class MergeGuestCommerce
{
    public function __construct(
        private readonly GuestDevice $guestDevice,
        private readonly CartService $cart,
        private readonly WishlistService $wishlist,
    ) {}

    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;
        $request = request();

        if (! $request instanceof Request) {
            return;
        }

        $token = $this->guestDevice->peek($request);

        if ($token === null) {
            return;
        }

        $this->cart->mergeGuestToUser($token, $user);
        $this->wishlist->mergeGuestToUser($token, $user);
        $this->guestDevice->forget();
    }
}
