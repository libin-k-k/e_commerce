<?php

namespace Tests\Feature\Modules\Admin;

use App\Models\User;
use App\Modules\Banner\Enums\BannerNavigationLink;
use App\Modules\Banner\Enums\BannerPosition;
use App\Modules\Banner\Enums\BannerStyle;
use App\Modules\Banner\Models\Banner;
use App\Modules\Banner\Models\BannerSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BannerSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_a_gif_banner_without_converting_it(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        $this->actingAs($admin)
            ->post(route('admin.banners.store'), [
                'title' => 'Animated hero',
                'position' => BannerPosition::Hero->value,
                'style' => BannerStyle::Cinematic->value,
                'navigation_link' => BannerNavigationLink::None->value,
                'is_active' => true,
                'display_width' => 640,
                'display_height' => 280,
                'web_image' => UploadedFile::fake()->createWithContent('spark.gif', $gif),
            ])
            ->assertRedirect(route('admin.banners.index'));

        $banner = Banner::query()->where('title', 'Animated hero')->first();
        $this->assertNotNull($banner);
        $this->assertStringEndsWith('.gif', (string) $banner->web_image_path);
        $this->assertSame(640, $banner->display_width);
        $this->assertSame(280, $banner->display_height);
        $this->assertTrue(Storage::disk('public')->exists($banner->web_image_path));
    }

    public function test_admin_can_drag_banners_into_a_new_order(): void
    {
        $admin = User::factory()->admin()->create();
        $first = Banner::factory()->create(['title' => 'First', 'sort_order' => 0]);
        $second = Banner::factory()->create(['title' => 'Second', 'sort_order' => 1]);

        $this->actingAs($admin)
            ->post(route('admin.banners.reorder'), [
                'ids' => [$second->id, $first->id],
            ])
            ->assertRedirect(route('admin.banners.index'));

        $this->assertSame(0, $second->refresh()->sort_order);
        $this->assertSame(1, $first->refresh()->sort_order);
    }

    public function test_disabling_a_banner_section_hides_it_and_the_count_flag_is_shared(): void
    {
        $admin = User::factory()->admin()->create();
        Banner::factory()->create([
            'title' => 'Hidden hero',
            'position' => BannerPosition::Hero,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.banners.sections.update'), [
                'sections' => [
                    ['position' => 'hero', 'is_enabled' => false, 'show_count' => true],
                    ['position' => 'middle', 'is_enabled' => true, 'show_count' => false],
                    ['position' => 'footer', 'is_enabled' => true, 'show_count' => false],
                    ['position' => 'offer_zone', 'is_enabled' => true, 'show_count' => false],
                    ['position' => 'product_listing', 'is_enabled' => true, 'show_count' => false],
                    ['position' => 'home_deals', 'is_enabled' => true, 'show_count' => false],
                ],
            ])
            ->assertRedirect(route('admin.banners.index'));

        $this->assertFalse(BannerSection::query()->where('position', 'hero')->first()->is_enabled);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('heroBanners', 0));
    }
}
