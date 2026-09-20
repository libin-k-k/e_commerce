<?php

namespace App\Modules\Admin\Http\Controllers\Auth;

use App\Core\Seo\SeoJsonLoader;
use App\Http\Controllers\Controller;
use App\Modules\Admin\Http\Requests\AdminLoginRequest;
use App\Modules\Admin\Services\AdminAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function __construct(
        private readonly AdminAuthService $adminAuthService,
        private readonly SeoJsonLoader $seoJsonLoader,
    ) {}

    public function create(): Response|RedirectResponse
    {
        if (Auth::check() && Auth::user()?->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('Admin/Pages/Login', [
            'seo' => $this->seoJsonLoader->load('Modules/Admin/login'),
        ]);
    }

    public function store(AdminLoginRequest $request): RedirectResponse
    {
        $this->adminAuthService->attempt($request->validated());

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->adminAuthService->logout();

        return redirect()->route('admin.login');
    }
}
