<?php

namespace App\Core\Commerce;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class GuestDevice
{
    public const CookieName = 'onecart_guest';

    public const CookieMinutes = 60 * 24 * 365;

    /**
     * @return array{user_id: ?int, guest_token: ?string}
     */
    public function owner(Request $request): array
    {
        $userId = Auth::id();

        if ($userId !== null) {
            return [
                'user_id' => (int) $userId,
                'guest_token' => null,
            ];
        }

        return [
            'user_id' => null,
            'guest_token' => $this->token($request),
        ];
    }

    public function token(Request $request): string
    {
        $existing = $request->cookie(self::CookieName);

        if (is_string($existing) && preg_match('/^[a-f0-9\-]{36}$/i', $existing) === 1) {
            return $existing;
        }

        $token = (string) Str::uuid();

        Cookie::queue(
            cookie(
                self::CookieName,
                $token,
                self::CookieMinutes,
                '/',
                null,
                $request->isSecure(),
                true,
                false,
                'Lax',
            ),
        );

        return $token;
    }

    public function peek(Request $request): ?string
    {
        $existing = $request->cookie(self::CookieName);

        if (is_string($existing) && preg_match('/^[a-f0-9\-]{36}$/i', $existing) === 1) {
            return $existing;
        }

        return null;
    }

    public function forget(): void
    {
        Cookie::queue(Cookie::forget(self::CookieName));
    }
}
