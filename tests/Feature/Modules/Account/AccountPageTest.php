<?php

namespace Tests\Feature\Modules\Account;

use App\Models\User;
use App\Modules\Account\Database\Seeders\CustomerUserSeeder;
use App\Modules\Account\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountPageTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        $this->seed(CustomerUserSeeder::class);

        return User::query()->where('email', 'customer@mail.com')->firstOrFail();
    }

    public function test_guest_account_page_lists_login_about_and_policies(): void
    {
        $this->get(route('account.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Account/Pages/Index')
                ->where('profile.isGuest', true)
                ->where('menu.0.key', 'login')
                ->where('menu.0.href', '/login')
                ->where('menu.4.key', 'about')
                ->where('menu.6.key', 'privacy')
                ->where('menu.7.key', 'terms')
                ->where('menu.8.key', 'shipping')
                ->where('menu.9.key', 'refund')
                ->has('menu', 10)
            );
    }

    public function test_account_index_renders(): void
    {
        $this->actingAs($this->customer())
            ->get(route('account.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Account/Pages/Index')
                ->has('seo.title')
                ->has('profile.name')
                ->has('menu', 9)
                ->where('menu.3.key', 'about')
                ->where('menu.5.key', 'privacy')
                ->where('menu.8.key', 'refund')
            );
    }

    public function test_account_profile_renders(): void
    {
        $this->actingAs($this->customer())
            ->get(route('account.profile'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Account/Pages/Profile')
                ->has('profile.email')
            );
    }

    public function test_customer_can_update_profile(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)
            ->put(route('account.profile.update'), [
                'name' => 'Updated Customer',
                'email' => 'updated@mail.com',
                'mobile' => '7708782197',
            ])
            ->assertRedirect(route('account.profile'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Updated Customer',
            'email' => 'updated@mail.com',
            'mobile' => '7708782197',
        ]);
    }

    public function test_account_addresses_renders(): void
    {
        $this->actingAs($this->customer())
            ->get(route('account.addresses'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Account/Pages/Addresses')
                ->has('addresses')
            );
    }

    public function test_customer_can_add_update_and_delete_address(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)
            ->post(route('account.addresses.store'), [
                'label' => 'Other',
                'full_address' => '99 New Street',
                'pincode' => '560002',
                'district' => 'Bengaluru Urban',
                'state' => 'Karnataka',
                'is_default' => false,
            ])
            ->assertRedirect(route('account.addresses'));

        $address = UserAddress::query()
            ->where('user_id', $customer->id)
            ->where('label', 'Other')
            ->firstOrFail();

        $this->actingAs($customer)
            ->put(route('account.addresses.update', $address), [
                'label' => 'Work',
                'full_address' => '99 Updated Street',
                'pincode' => '560003',
                'district' => 'Bengaluru Urban',
                'state' => 'Karnataka',
                'is_default' => true,
            ])
            ->assertRedirect(route('account.addresses'));

        $this->assertDatabaseHas('user_addresses', [
            'id' => $address->id,
            'full_address' => '99 Updated Street',
            'is_default' => true,
        ]);

        $this->actingAs($customer)
            ->delete(route('account.addresses.destroy', $address))
            ->assertRedirect(route('account.addresses'));

        $this->assertDatabaseMissing('user_addresses', [
            'id' => $address->id,
        ]);
    }

    public function test_customer_can_logout(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_account_orders_renders(): void
    {
        $this->actingAs($this->customer())
            ->get(route('account.orders'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Account/Pages/Orders')
                ->has('orders')
            );
    }

    public function test_account_faq_renders(): void
    {
        $this->get(route('account.faq'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Account/Pages/Faq')
                ->has('faq')
            );
    }

    public function test_account_policies_renders(): void
    {
        $this->get(route('account.policies'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Account/Pages/Policies')
                ->has('policies', 4)
                ->where('policies.0.href', '/terms-and-conditions')
            );
    }

    public function test_about_page_lists_login_and_social_accounts_for_guests(): void
    {
        $this->get(route('account.about'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Account/Pages/About')
                ->has('seo.title')
                ->has('about.intro')
                ->has('about.socials', 4)
                ->where('about.socials.0.name', 'Instagram')
                ->where('about.socials.1.name', 'Facebook')
                ->where('about.socials.2.name', 'Telegram')
                ->where('about.socials.3.name', 'WhatsApp')
                ->where('menu.0.key', 'login')
                ->where('menu.1.title', 'Profile')
                ->where('appName', config('app.name'))
            );
    }

    public function test_payment_policy_pages_are_public(): void
    {
        $pages = [
            'legal.privacy' => 'Privacy policy',
            'legal.terms' => 'Terms and conditions',
            'legal.shipping' => 'Shipping policy',
            'legal.refund' => 'Refund policy',
        ];

        foreach ($pages as $route => $title) {
            $this->get(route($route))
                ->assertOk()
                ->assertInertia(fn ($page) => $page
                    ->component('Account/Pages/Legal')
                    ->where('document.title', $title)
                    ->has('document.sections')
                    ->has('seo.description')
                );
        }
    }
}
