<?php

namespace App\Modules\Account\Http\Controllers\Auth;

use App\Core\Seo\SeoJsonLoader;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function __construct(private readonly SeoJsonLoader $seoJsonLoader) {}

    public function create(): Response
    {
        return Inertia::render('Account/Pages/Auth/Login', [
            'seo' => $this->seoJsonLoader->load('Modules/Account/login'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $login = trim($data['login']);
        $user = $this->findByEmailOrMobile($login);

        if ($user === null || ! Hash::check($data['password'], $user->password)) {
            return back()->withErrors([
                'login' => 'These credentials do not match our records.',
            ])->onlyInput('login');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->is_admin) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('account.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function findByEmailOrMobile(string $login): ?User
    {
        $normalizedMobile = preg_replace('/\D+/', '', $login) ?? '';

        return User::query()
            ->where(function ($query) use ($login, $normalizedMobile): void {
                $query->where('email', $login);

                if ($normalizedMobile !== '') {
                    $query->orWhere('mobile', $normalizedMobile)
                        ->orWhere('mobile', $login);
                }
            })
            ->first();
    }
}
