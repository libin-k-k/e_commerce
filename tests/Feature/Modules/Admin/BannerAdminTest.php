<?php

namespace Tests\Feature\Modules\Admin;

use App\Models\User;
use App\Modules\Banner\Enums\BannerNavigationLink;
use App\Modules\Banner\Enums\BannerPosition;
use App\Modules\Banner\Enums\BannerStyle;
use App\Modules\Banner\Models\Banner;
use App\Modules\Banner\Services\WebpImageConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BannerAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_banners_index(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.banners.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Pages/Banners/Index')
                ->has('banners')
                ->has('options.positions')
            );
    }

    public function test_admin_can_create_banner_with_webp_conversion(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $web = UploadedFile::fake()->image('web.jpg', 1600, 700);
        $mobile = UploadedFile::fake()->image('mobile.jpg', 800, 1000);

        $this->actingAs($admin)
            ->post(route('admin.banners.store'), [
                'title' => 'Hero Launch',
                'subtitle' => 'New season picks',
                'position' => BannerPosition::Hero->value,
                'style' => BannerStyle::DarkSplit->value,
                'navigation_link' => BannerNavigationLink::Products->value,
                'button_text' => 'Shop Now',
                'sort_order' => 1,
                'is_active' => true,
                'web_image' => $web,
                'mobile_image' => $mobile,
            ])
            ->assertRedirect(route('admin.banners.index'));

        $banner = Banner::query()->where('title', 'Hero Launch')->first();
        $this->assertNotNull($banner);
        $this->assertNotNull($banner->web_image_path);
        $this->assertNotNull($banner->mobile_image_path);
        $this->assertStringEndsWith('.webp', $banner->web_image_path);
        $this->assertStringEndsWith('.webp', $banner->mobile_image_path);
        $this->assertTrue(Storage::disk('public')->exists($banner->web_image_path));
        $this->assertLessThanOrEqual(200 * 1024, Storage::disk('public')->size($banner->web_image_path));
        $this->assertLessThanOrEqual(200 * 1024, Storage::disk('public')->size($banner->mobile_image_path));
    }

    public function test_admin_can_create_banner_with_only_web_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.banners.store'), [
                'title' => 'Web Only',
                'position' => BannerPosition::Hero->value,
                'style' => BannerStyle::Cinematic->value,
                'navigation_link' => BannerNavigationLink::None->value,
                'sort_order' => 0,
                'is_active' => true,
                'web_image' => UploadedFile::fake()->image('web.jpg', 1200, 500),
            ])
            ->assertRedirect(route('admin.banners.index'));

        $banner = Banner::query()->where('title', 'Web Only')->first();
        $this->assertNotNull($banner);
        $this->assertNotNull($banner->web_image_path);
        $this->assertNull($banner->mobile_image_path);
    }

    public function test_admin_can_create_banner_with_only_mobile_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.banners.store'), [
                'title' => 'Mobile Only',
                'position' => BannerPosition::Middle->value,
                'style' => BannerStyle::Gradient->value,
                'navigation_link' => BannerNavigationLink::Sale->value,
                'sort_order' => 0,
                'is_active' => true,
                'mobile_image' => UploadedFile::fake()->image('mobile.jpg', 800, 1000),
            ])
            ->assertRedirect(route('admin.banners.index'));

        $banner = Banner::query()->where('title', 'Mobile Only')->first();
        $this->assertNotNull($banner);
        $this->assertNull($banner->web_image_path);
        $this->assertNotNull($banner->mobile_image_path);
    }

    public function test_banner_create_requires_at_least_one_image(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.banners.store'), [
                'title' => 'No Image',
                'position' => BannerPosition::Hero->value,
                'style' => BannerStyle::Cinematic->value,
                'navigation_link' => BannerNavigationLink::None->value,
                'sort_order' => 0,
                'is_active' => true,
            ])
            ->assertSessionHasErrors(['web_image', 'mobile_image']);
    }

    public function test_webp_converter_keeps_output_under_200kb(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('large.jpg', 2400, 1200);
        $path = app(WebpImageConverter::class)->store($file, 'banners/test', 1920);

        $this->assertStringEndsWith('.webp', $path);
        $this->assertLessThanOrEqual(200 * 1024, Storage::disk('public')->size($path));
    }

    public function test_admin_can_update_banner(): void
    {
        $admin = User::factory()->admin()->create();
        $banner = Banner::factory()->create([
            'title' => 'Old title',
            'position' => BannerPosition::Middle,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.banners.update', $banner), [
                'title' => 'Updated title',
                'subtitle' => 'Updated subtitle',
                'position' => BannerPosition::Footer->value,
                'style' => BannerStyle::Gradient->value,
                'navigation_link' => BannerNavigationLink::Home->value,
                'button_text' => 'Explore',
                'sort_order' => 3,
                'is_active' => false,
            ])
            ->assertRedirect(route('admin.banners.index'));

        $this->assertDatabaseHas('banners', [
            'id' => $banner->id,
            'title' => 'Updated title',
            'position' => 'footer',
            'is_active' => 0,
        ]);
    }

    public function test_admin_can_delete_banner(): void
    {
        $admin = User::factory()->admin()->create();
        $banner = Banner::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.banners.destroy', $banner))
            ->assertRedirect(route('admin.banners.index'));

        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
    }
}
