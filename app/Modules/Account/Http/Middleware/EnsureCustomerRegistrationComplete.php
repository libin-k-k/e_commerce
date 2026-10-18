<?php

namespace App\Modules\Account\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerRegistrationComplete
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only incomplete Google sign-ups are gated; password registrations already require mobile + address.
        if (
            $user === null
            || $user->is_admin
            || $user->google_id === null
            || $user->hasCompletedRegistration()
        ) {
            return $next($request);
        }

        if ($request->routeIs(
            'register.google.complete',
            'register.google.complete.store',
            'logout',
        )) {
            return $next($request);
        }

        return redirect()->route('register.google.complete');
    }
}
