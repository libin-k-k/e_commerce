<?php

namespace App\Modules\Account\Http\Controllers;

use App\Core\Seo\SeoJsonLoader;
use App\Http\Controllers\Controller;
use App\Modules\Account\Http\Requests\UpdateProfileRequest;
use App\Modules\Account\Services\AccountPageService;
use App\Modules\Account\Services\AccountProfileService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function __construct(
        private readonly AccountPageService $accountPageService,
        private readonly AccountProfileService $accountProfileService,
        private readonly SeoJsonLoader $seoJsonLoader,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Account/Pages/Index', $this->page('Modules/Account/index', [
            'profile' => $this->accountPageService->profile(),
            'active' => 'index',
        ]));
    }

    public function profile(): Response
    {
        return Inertia::render('Account/Pages/Profile', $this->page('Modules/Account/profile', [
            'profile' => $this->accountPageService->profile(),
            'active' => 'profile',
        ]));
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $this->accountProfileService->updateProfile(
            $request->user(),
            $request->safe()->only(['name', 'email', 'mobile']),
        );

        return redirect()
            ->route('account.profile')
            ->with('success', 'Profile updated.');
    }

    public function addresses(): Response
    {
        return Inertia::render('Account/Pages/Addresses', $this->page('Modules/Account/addresses', [
            'addresses' => $this->accountPageService->addresses(),
            'addressLabels' => ['Home', 'Work', 'Other'],
            'active' => 'addresses',
        ]));
    }

    public function orders(): Response
    {
        return Inertia::render('Account/Pages/Orders', $this->page('Modules/Account/orders', [
            'orders' => $this->accountPageService->orders(),
            'active' => 'orders',
        ]));
    }

    public function faq(): Response
    {
        return Inertia::render('Account/Pages/Faq', $this->page('Modules/Account/faq', [
            'faq' => $this->accountPageService->faq(),
            'active' => 'faq',
        ]));
    }

    public function policies(): Response
    {
        return Inertia::render('Account/Pages/Policies', $this->page('Modules/Account/policies', [
            'policies' => $this->accountPageService->policies(),
            'active' => 'policies',
        ]));
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function page(string $seoKey, array $extra = []): array
    {
        return [
            'seo' => $this->seoJsonLoader->load($seoKey),
            'menu' => $this->accountPageService->menu(),
            'profile' => $this->accountPageService->profile(),
            ...$extra,
        ];
    }
}
