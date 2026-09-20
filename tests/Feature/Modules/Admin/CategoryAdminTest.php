<?php

namespace Tests\Feature\Modules\Admin;

use App\Models\User;
use App\Modules\Category\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CategoryAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_categories_index(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->create(['name' => 'Western Wear', 'slug' => 'western-wear']);

        $this->actingAs($admin)
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Pages/Categories/Index')
                ->has('categories', 1)
                ->where('categories.0.name', 'Western Wear')
            );
    }

    public function test_admin_can_create_main_category_with_webp_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Beauty',
                'slug' => 'beauty',
                'description' => 'Glow essentials',
                'parent_id' => null,
                'sort_order' => 1,
                'is_active' => true,
                'image' => UploadedFile::fake()->image('beauty.jpg', 800, 800),
            ])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::query()->where('slug', 'beauty')->first();
        $this->assertNotNull($category);
        $this->assertNull($category->parent_id);
        $this->assertStringEndsWith('.webp', (string) $category->image_path);
        $this->assertTrue(Storage::disk('public')->exists($category->image_path));
        $this->assertLessThanOrEqual(200 * 1024, Storage::disk('public')->size($category->image_path));
    }

    public function test_admin_can_create_sub_category(): void
    {
        $admin = User::factory()->admin()->create();
        $parent = Category::factory()->create(['name' => 'Western Wear', 'slug' => 'western-wear']);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Tops',
                'slug' => 'western-tops',
                'parent_id' => $parent->id,
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'slug' => 'western-tops',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_admin_cannot_delete_main_category_with_children(): void
    {
        $admin = User::factory()->admin()->create();
        $parent = Category::factory()->create();
        Category::factory()->childOf($parent)->create();

        $this->actingAs($admin)
            ->from(route('admin.categories.index'))
            ->delete(route('admin.categories.destroy', $parent))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('categories', ['id' => $parent->id]);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'New Name',
                'slug' => 'new-name',
                'parent_id' => null,
                'sort_order' => 2,
                'is_active' => false,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Name',
            'slug' => 'new-name',
            'is_active' => 0,
        ]);
    }
}
