<?php

namespace App\Modules\Account\Http\Controllers\Auth;

use App\Core\Seo\SeoJsonLoader;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    public function __construct(private readonly SeoJsonLoader $seoJsonLoader) {}

    public function create(): Response
    {
        return Inertia::render('Account/Pages/Auth/Register', [
            'seo' => $this->seoJsonLoader->load('Modules/Account/register'),
            'addressLabels' => ['Home', 'Work', 'Other'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $mobile = preg_replace('/\D+/', '', (string) $request->input('mobile', '')) ?: '';
        $email = trim((string) $request->input('email', ''));
        $email = $email !== '' ? $email : null;

        $request->merge([
            'mobile' => $mobile,
            'email' => $email,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['required', 'string', 'digits_between:10,15', Rule::unique('users', 'mobile')],
            'password' => ['required', 'confirmed', Password::defaults()],
            'addresses' => ['required', 'array', 'min:1'],
            'addresses.*.label' => ['required', 'string', 'max:40'],
            'addresses.*.full_address' => ['required', 'string', 'max:500'],
            'addresses.*.pincode' => ['required', 'string', 'max:12'],
            'addresses.*.district' => ['required', 'string', 'max:120'],
            'addresses.*.state' => ['required', 'string', 'max:120'],
            'addresses.*.is_default' => ['sometimes', 'boolean'],
        ]);

        $user = DB::transaction(function () use ($data): User {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'mobile' => $data['mobile'],
                'password' => $data['password'],
                'is_admin' => false,
            ]);

            $addresses = array_values($data['addresses']);
            $explicitDefault = collect($addresses)->contains(fn (array $row): bool => (bool) ($row['is_default'] ?? false));
            $hasDefault = false;

            foreach ($addresses as $index => $address) {
                $isDefault = (bool) ($address['is_default'] ?? false);
                if (! $explicitDefault && $index === 0) {
                    $isDefault = true;
                }
                if ($hasDefault) {
                    $isDefault = false;
                }
                if ($isDefault) {
                    $hasDefault = true;
                }

                $user->addresses()->create([
                    'label' => trim((string) $address['label']),
                    'full_address' => trim((string) $address['full_address']),
                    'pincode' => trim((string) $address['pincode']),
                    'district' => trim((string) $address['district']),
                    'state' => trim((string) $address['state']),
                    'is_default' => $isDefault,
                ]);
            }

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.index');
    }
}
