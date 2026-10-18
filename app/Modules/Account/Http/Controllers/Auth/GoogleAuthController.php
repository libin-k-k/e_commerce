<?php

namespace App\Modules\Account\Http\Controllers\Auth;

use App\Core\Seo\SeoJsonLoader;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Account\Http\Requests\CompleteGoogleRegistrationRequest;
use App\Modules\Account\Services\GoogleAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class GoogleAuthController extends Controller
{
    public function __construct(
        private readonly GoogleAuthService $googleAuthService,
        private readonly SeoJsonLoader $seoJsonLoader,
    ) {}

    public function redirect(): SymfonyRedirectResponse|RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Google sign-in failed. Please try again.']);
        }

        $user = $this->googleAuthService->findOrCreateFromGoogle($googleUser);

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        if (! $user->hasCompletedRegistration()) {
            return redirect()->route('register.google.complete');
        }

        return redirect()->intended(route('account.index'));
    }

    public function completeForm(): Response|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasCompletedRegistration()) {
            return redirect()->route('account.index');
        }

        return Inertia::render('Account/Pages/Auth/CompleteGoogleRegistration', [
            'seo' => $this->seoJsonLoader->load('Modules/Account/complete-google-registration'),
            'addressLabels' => ['Home', 'Work', 'Other'],
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function complete(CompleteGoogleRegistrationRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasCompletedRegistration()) {
            return redirect()->route('account.index');
        }

        $this->googleAuthService->completeRegistration($user, $request->validatedPayload());

        return redirect()
            ->route('account.index')
            ->with('success', 'Registration completed. Welcome to '.config('app.name').'!');
    }
}
