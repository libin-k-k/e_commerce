<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Account\Database\Seeders\CustomerUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Account/Pages/Auth/Login'));
    }

    public function test_register_page_renders(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Account/Pages/Auth/Register')
                ->has('addressLabels'));
    }

    public function test_customer_seeder_creates_demo_customer_with_addresses(): void
    {
        $this->seed(CustomerUserSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'customer@mail.com',
            'mobile' => '7708782197',
            'is_admin' => false,
        ]);

        $customer = User::query()->where('email', 'customer@mail.com')->firstOrFail();
        $this->assertSame(2, $customer->addresses()->count());
    }

    public function test_customer_can_login_with_email(): void
    {
        $this->seed(CustomerUserSeeder::class);

        $this->post(route('login.store'), [
            'login' => 'customer@mail.com',
            'password' => 'Admin@123',
        ])->assertRedirect(route('account.index'));

        $this->assertAuthenticated();
    }

    public function test_customer_can_login_with_mobile(): void
    {
        $this->seed(CustomerUserSeeder::class);

        $this->post(route('login.store'), [
            'login' => '7708782197',
            'password' => 'Admin@123',
        ])->assertRedirect(route('account.index'));

        $this->assertAuthenticated();
    }

    public function test_customer_can_register_with_nullable_email_and_addresses(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'New Shopper',
            'email' => null,
            'mobile' => '9876543210',
            'password' => 'Admin@123',
            'password_confirmation' => 'Admin@123',
            'addresses' => [
                [
                    'label' => 'Home',
                    'full_address' => '12 Market Street',
                    'pincode' => '600001',
                    'district' => 'Chennai',
                    'state' => 'Tamil Nadu',
                    'is_default' => true,
                ],
                [
                    'label' => 'Work',
                    'full_address' => '88 IT Corridor',
                    'pincode' => '600096',
                    'district' => 'Chennai',
                    'state' => 'Tamil Nadu',
                    'is_default' => false,
                ],
            ],
        ]);

        $response->assertRedirect(route('account.index'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'name' => 'New Shopper',
            'email' => null,
            'mobile' => '9876543210',
            'is_admin' => false,
        ]);

        $user = User::query()->where('mobile', '9876543210')->firstOrFail();
        $this->assertSame(2, $user->addresses()->count());
        $this->assertDatabaseHas('user_addresses', [
            'user_id' => $user->id,
            'label' => 'Home',
            'pincode' => '600001',
            'is_default' => true,
        ]);
    }

    public function test_account_addresses_use_saved_customer_addresses(): void
    {
        $this->seed(CustomerUserSeeder::class);
        $customer = User::query()->where('email', 'customer@mail.com')->firstOrFail();

        $this->actingAs($customer)
            ->get(route('account.addresses'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Account/Pages/Addresses')
                ->has('addresses', 2)
                ->where('addresses.0.label', 'Home')
                ->where('addresses.0.pincode', '560001'));
    }
}
