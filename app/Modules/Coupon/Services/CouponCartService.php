<?php

namespace App\Modules\Coupon\Services;

use App\Models\User;
use App\Modules\Coupon\Enums\CouponType;
use App\Modules\Coupon\Models\Coupon;
use App\Modules\Coupon\Models\CouponRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CouponCartService
{
    public const SessionKey = 'cart_coupon_code';

    /**
     * @param  list<array<string, mixed>>  $cartItems
     * @return array{
     *     code: string,
     *     name: ?string,
     *     type: string,
     *     value: float,
     *     discount: float,
     *     label: string
     * }|null
     */
    public function applied(Request $request, array $cartItems, float $subtotal): ?array
    {
        $code = $request->session()->get(self::SessionKey);
        if (! is_string($code) || trim($code) === '') {
            return null;
        }

        try {
            $result = $this->evaluate($code, $request->user(), $cartItems, $subtotal);
        } catch (ValidationException) {
            $request->session()->forget(self::SessionKey);

            return null;
        }

        return $result;
    }

    /**
     * @param  list<array<string, mixed>>  $cartItems
     * @return array{
     *     code: string,
     *     name: ?string,
     *     type: string,
     *     value: float,
     *     discount: float,
     *     label: string
     * }
     */
    public function apply(Request $request, string $code, array $cartItems, float $subtotal): array
    {
        // One coupon only: evaluating + putting always replaces any previous session coupon.
        $result = $this->evaluate($code, $request->user(), $cartItems, $subtotal);
        $request->session()->put(self::SessionKey, $result['code']);

        return $result;
    }

    public function remove(Request $request): void
    {
        $request->session()->forget(self::SessionKey);
    }

    /**
     * Eligible coupons for the cart picker modal (applicable to the current cart only).
     *
     * @param  list<array<string, mixed>>  $cartItems
     * @return list<array<string, mixed>>
     */
    public function availableOffers(Request $request, array $cartItems, float $subtotal): array
    {
        $appliedCode = strtoupper((string) $request->session()->get(self::SessionKey, ''));
        $user = $request->user();

        return Coupon::query()
            ->with('products:id')
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get()
            ->filter(fn (Coupon $coupon): bool => $coupon->isWithinValidity())
            ->map(function (Coupon $coupon) use ($user, $cartItems, $subtotal, $appliedCode): ?array {
                try {
                    $result = $this->evaluateCoupon($coupon, $user, $cartItems, $subtotal);
                } catch (ValidationException) {
                    return null;
                }

                return [
                    'code' => $coupon->code,
                    'name' => $coupon->name ?: 'Coupon',
                    'type' => $coupon->type->value,
                    'value' => (float) $coupon->value,
                    'label' => $this->label($coupon),
                    'headline' => $this->headline($coupon),
                    'expires_label' => $this->expiresLabel($coupon),
                    'applied' => $appliedCode !== '' && $appliedCode === $coupon->code,
                    'applicable' => true,
                    'discount' => $result['discount'],
                    'reason' => null,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Record a completed redemption (call from checkout when orders exist).
     */
    public function redeem(Coupon $coupon, ?User $user, ?string $guestToken, float $discount): CouponRedemption
    {
        return CouponRedemption::query()->create([
            'coupon_id' => $coupon->id,
            'user_id' => $user?->id,
            'guest_token' => $guestToken,
            'discount_amount' => $discount,
            'status' => 'completed',
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $cartItems
     * @return array{
     *     code: string,
     *     name: ?string,
     *     type: string,
     *     value: float,
     *     discount: float,
     *     label: string
     * }
     */
    private function evaluate(string $code, ?User $user, array $cartItems, float $subtotal): array
    {
        $code = strtoupper(trim($code));
        $coupon = Coupon::query()
            ->with('products:id')
            ->where('code', $code)
            ->first();

        if ($coupon === null || ! $coupon->is_active) {
            throw ValidationException::withMessages(['code' => 'This coupon is invalid.']);
        }

        return $this->evaluateCoupon($coupon, $user, $cartItems, $subtotal);
    }

    /**
     * @param  list<array<string, mixed>>  $cartItems
     * @return array{
     *     code: string,
     *     name: ?string,
     *     type: string,
     *     value: float,
     *     discount: float,
     *     label: string
     * }
     */
    private function evaluateCoupon(Coupon $coupon, ?User $user, array $cartItems, float $subtotal): array
    {
        if (! $coupon->isWithinValidity()) {
            throw ValidationException::withMessages(['code' => 'This coupon is outside its validity period.']);
        }

        if ($coupon->min_order_amount !== null && $subtotal < (float) $coupon->min_order_amount) {
            throw ValidationException::withMessages([
                'code' => 'Minimum order amount for this coupon is ₹'.number_format((float) $coupon->min_order_amount, 2).'.',
            ]);
        }

        if ($coupon->max_order_amount !== null && $subtotal > (float) $coupon->max_order_amount) {
            throw ValidationException::withMessages([
                'code' => 'Maximum order amount for this coupon is ₹'.number_format((float) $coupon->max_order_amount, 2).'.',
            ]);
        }

        if ($coupon->first_order_only) {
            if ($user === null) {
                throw ValidationException::withMessages(['code' => 'Sign in to use this first-order coupon.']);
            }

            if ($this->userHasPriorOrders($user)) {
                throw ValidationException::withMessages(['code' => 'This coupon is only for first orders.']);
            }
        }

        if ($coupon->total_usage_limit !== null) {
            $used = $coupon->redemptions()->where('status', 'completed')->count();
            if ($used >= $coupon->total_usage_limit) {
                throw ValidationException::withMessages(['code' => 'This coupon has reached its total usage limit.']);
            }
        }

        if ($coupon->per_user_usage_limit !== null) {
            if ($user === null) {
                throw ValidationException::withMessages(['code' => 'Sign in to use this coupon.']);
            }

            $userUsed = $coupon->redemptions()
                ->where('status', 'completed')
                ->where('user_id', $user->id)
                ->count();

            if ($userUsed >= $coupon->per_user_usage_limit) {
                throw ValidationException::withMessages(['code' => 'You have already used this coupon the maximum number of times.']);
            }
        }

        $eligibleSubtotal = $this->eligibleSubtotal($coupon, $cartItems);
        if ($eligibleSubtotal <= 0) {
            throw ValidationException::withMessages(['code' => 'This coupon does not apply to the products in your cart.']);
        }

        $discount = $this->calculateDiscount($coupon, $eligibleSubtotal);
        if ($discount <= 0) {
            throw ValidationException::withMessages(['code' => 'This coupon does not provide a discount for your cart.']);
        }

        $discount = min($discount, $eligibleSubtotal);

        return [
            'code' => $coupon->code,
            'name' => $coupon->name,
            'type' => $coupon->type->value,
            'value' => (float) $coupon->value,
            'discount' => round($discount, 2),
            'label' => $this->label($coupon),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $cartItems
     */
    private function eligibleSubtotal(Coupon $coupon, array $cartItems): float
    {
        $productIds = $coupon->products->pluck('id')->map(fn ($id): int => (int) $id)->all();
        $restrict = $productIds !== [];

        $total = 0.0;
        foreach ($cartItems as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            if ($restrict && ! in_array($productId, $productIds, true)) {
                continue;
            }
            $total += (float) ($item['line_total'] ?? 0);
        }

        return round($total, 2);
    }

    private function calculateDiscount(Coupon $coupon, float $eligibleSubtotal): float
    {
        if ($coupon->type === CouponType::Fixed) {
            return round(min((float) $coupon->value, $eligibleSubtotal), 2);
        }

        $raw = $eligibleSubtotal * ((float) $coupon->value / 100);
        $capped = $coupon->max_discount_amount !== null
            ? min($raw, (float) $coupon->max_discount_amount)
            : $raw;

        return round(max(0.0, $capped), 2);
    }

    private function label(Coupon $coupon): string
    {
        if ($coupon->type === CouponType::Percent) {
            $label = rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.').'% off';
            if ($coupon->max_discount_amount !== null) {
                $label .= ' (max ₹'.number_format((float) $coupon->max_discount_amount, 2).')';
            }

            return $label;
        }

        return '₹'.number_format((float) $coupon->value, 2).' off';
    }

    private function headline(Coupon $coupon): string
    {
        if ($coupon->type === CouponType::Percent) {
            return rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.').'% off';
        }

        return '₹'.rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.').' off';
    }

    private function expiresLabel(Coupon $coupon): string
    {
        if ($coupon->ends_at === null) {
            return 'No expiry';
        }

        return 'Expiry: '.$coupon->ends_at->format('Y-m-d');
    }

    private function userHasPriorOrders(User $user): bool
    {
        if (! Schema::hasTable('orders')) {
            return false;
        }

        return DB::table('orders')->where('user_id', $user->id)->exists();
    }
}
