<?php

namespace Tests\Feature\Modules\Account;

use App\Models\User;
use App\Modules\Account\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_route_is_available(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $this->get(route('auth.google'))
            ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_first_time_google_user_must_complete_mobile_and_address(): void
    {
        $this->fakeGoogleUser([
            'id' => 'google-100',
            'name' => 'Google Shopper',
            'email' => 'google.shopper@example.com',
        ]);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('register.google.complete'));

        $user = User::query()->where('email', 'google.shopper@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('google-100', $user->google_id);
        $this->assertNull($user->mobile);
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->hasCompletedRegistration());

        $this->get(route('account.index'))
            ->assertRedirect(route('register.google.complete'));

        $this->get(route('register.google.complete'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Account/Pages/Auth/CompleteGoogleRegistration')
                ->where('profile.email', 'google.shopper@example.com')
            );

        $this->post(route('register.google.complete.store'), [
            'name' => 'Google Shopper',
            'mobile' => '9876543210',
            'addresses' => [
                [
                    'label' => 'Home',
                    'full_address' => '12 Market Road',
                    'pincode' => '560001',
                    'district' => 'Bengaluru Urban',
                    'state' => 'Karnataka',
                    'is_default' => true,
                ],
            ],
        ])->assertRedirect(route('account.index'));

        $user->refresh();
        $this->assertSame('9876543210', $user->mobile);
        $this->assertTrue($user->hasCompletedRegistration());
        $this->assertDatabaseHas('user_addresses', [
            'user_id' => $user->id,
            'full_address' => '12 Market Road',
            'is_default' => 1,
        ]);

        $this->get(route('account.index'))->assertOk();
    }

    public function test_returning_google_user_with_complete_profile_goes_to_account(): void
    {
        $user = User::factory()->create([
            'email' => 'returning@example.com',
            'google_id' => 'google-200',
            'mobile' => '9123456780',
        ]);

        UserAddress::query()->create([
            'user_id' => $user->id,
            'label' => 'Home',
            'full_address' => '1 Main Street',
            'pincode' => '560001',
            'district' => 'Bengaluru Urban',
            'state' => 'Karnataka',
            'is_default' => true,
        ]);

        $this->fakeGoogleUser([
            'id' => 'google-200',
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('account.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_google_login_links_existing_email_account(): void
    {
        $user = User::factory()->create([
            'email' => 'linked@example.com',
            'google_id' => null,
            'mobile' => '9111222333',
        ]);

        UserAddress::query()->create([
            'user_id' => $user->id,
            'label' => 'Home',
            'full_address' => '88 Ring Road',
            'pincode' => '560002',
            'district' => 'Bengaluru Urban',
            'state' => 'Karnataka',
            'is_default' => true,
        ]);

        $this->fakeGoogleUser([
            'id' => 'google-300',
            'name' => $user->name,
            'email' => 'linked@example.com',
        ]);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('account.index'));

        $this->assertSame('google-300', $user->fresh()->google_id);
        $this->assertAuthenticatedAs($user);
    }

    /**
     * @param  array{id: string, name: string, email: string}  $attributes
     */
    private function fakeGoogleUser(array $attributes): void
    {
        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getId')->andReturn($attributes['id']);
        $socialUser->shouldReceive('getName')->andReturn($attributes['name']);
        $socialUser->shouldReceive('getNickname')->andReturn(null);
        $socialUser->shouldReceive('getEmail')->andReturn($attributes['email']);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);
    }
}
