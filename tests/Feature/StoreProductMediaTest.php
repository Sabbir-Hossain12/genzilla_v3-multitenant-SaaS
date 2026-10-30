<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StoreProductMediaTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->user = User::create([
            'name' => 'Merchant Test',
            'email' => 'merchant@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->tenant = Tenant::create([
            'user_id' => $this->user->id,
            'store_name' => 'Test Store',
            'subdomain' => 'teststore',
            'subscription_status' => 'active',
            'status' => 'active',
        ]);

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->tenant->id);

        $role = Role::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Store Owner',
            'guard_name' => 'web',
        ]);

        $permissions = [
            'Store Product List',
            'Store Product Create',
            'Store Product Edit',
            'Store Product Delete',
        ];

        foreach ($permissions as $permName) {
            $perm = Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web'],
                ['group_name' => 'Catalog', 'panel' => 'store']
            );
            $role->givePermissionTo($perm);
        }

        $this->user->assignRole($role);
    }

    public function test_product_can_be_created_with_avif_thumbnail_and_slider_images(): void
    {
        $thumbnailFile = UploadedFile::fake()->create('thumbnail.avif', 250, 'image/avif');
        $slider1 = UploadedFile::fake()->image('slide1.png', 800, 600);
        $slider2 = UploadedFile::fake()->create('slide2.avif', 300, 'image/avif');

        $response = $this->actingAs($this->user)
            ->post($this->storeUrl('/store/catalog/products'), [
                'name' => 'Paracetamol 500mg',
                'slug' => 'paracetamol-500mg',
                'kind' => 'medicine',
                'status' => 'active',
                'generic_name' => 'Paracetamol',
                'price' => 15.50,
                'sku' => 'PARA-500',
                'image' => $thumbnailFile,
                'slider_images' => [$slider1, $slider2],
                'seo_title' => 'Paracetamol 500mg Tablet',
                'seo_description' => 'Fast acting pain relief tablets.',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('store.admin.products.index'));

        $product = Product::where('tenant_id', $this->tenant->id)->where('slug', 'paracetamol-500mg')->first();
        $this->assertNotNull($product);

        // Check single thumbnail media in database
        $thumbMedia = $product->media()->where('collection', 'thumbnail')->first();
        $this->assertNotNull($thumbMedia);
        $this->assertEquals(Product::class, $thumbMedia->model_type);
        $this->assertEquals($this->tenant->id, $thumbMedia->tenant_id);
        Storage::disk('public')->assertExists($thumbMedia->path);

        // Check multiple slider media in database
        $sliderMedia = $product->media()->where('collection', 'slider')->get();
        $this->assertCount(2, $sliderMedia);
        foreach ($sliderMedia as $slide) {
            $this->assertEquals(Product::class, $slide->model_type);
            $this->assertEquals($this->tenant->id, $slide->tenant_id);
            Storage::disk('public')->assertExists($slide->path);
        }

        // Test thumbnail_url & cover_image_url accessors
        $this->assertNotNull($product->thumbnail_url);
        $this->assertStringContainsString($thumbMedia->path, $product->thumbnail_url);
        $this->assertEquals($product->thumbnail_url, $product->cover_image_url);
    }

    public function test_product_media_can_be_updated_replaced_and_removed(): void
    {
        $oldThumb = UploadedFile::fake()->image('old_thumb.jpg');
        $oldSlide = UploadedFile::fake()->image('old_slide.jpg');

        $thumbPath = $oldThumb->store('tenants/' . $this->tenant->id . '/products/thumbnail', 'public');
        $slidePath = $oldSlide->store('tenants/' . $this->tenant->id . '/products/slider', 'public');

        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Vitamin C 1000mg',
            'slug' => 'vitamin-c-1000mg',
            'kind' => 'general',
            'status' => 'active',
        ]);

        $product->variants()->create([
            'tenant_id' => $this->tenant->id,
            'sku' => 'VIT-C-1000',
            'title' => 'Default',
            'price' => 20.00,
            'is_default' => true,
            'position' => 0,
            'status' => 'active',
            'options_hash' => md5('[]'),
        ]);

        $thumbRecord = $product->media()->create([
            'tenant_id' => $this->tenant->id,
            'model_type' => Product::class,
            'collection' => 'thumbnail',
            'path' => $thumbPath,
            'mime' => 'image/jpeg',
            'size_bytes' => 1024,
            'position' => 0,
            'alt' => 'Vitamin C 1000mg',
            'created_at' => now(),
        ]);

        $sliderRecord = $product->media()->create([
            'tenant_id' => $this->tenant->id,
            'model_type' => Product::class,
            'collection' => 'slider',
            'path' => $slidePath,
            'mime' => 'image/jpeg',
            'size_bytes' => 2048,
            'position' => 1,
            'alt' => 'Vitamin C Slide 1',
            'created_at' => now(),
        ]);

        $newThumb = UploadedFile::fake()->create('new_thumb.avif', 200, 'image/avif');
        $newSlide = UploadedFile::fake()->create('new_slide.avif', 400, 'image/avif');

        // Update product: replace thumbnail, delete old slider, add new slider
        $response = $this->actingAs($this->user)
            ->put($this->storeUrl('/store/catalog/products/' . $product->id), [
                'name' => 'Vitamin C 1000mg Plus Zinc',
                'slug' => 'vitamin-c-1000mg-plus-zinc',
                'kind' => 'general',
                'status' => 'active',
                'price' => 22.00,
                'sku' => 'VIT-C-1000',
                'image' => $newThumb,
                'remove_slider_images' => [$sliderRecord->id],
                'slider_images' => [$newSlide],
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('store.admin.products.index'));

        // Old thumbnail file and old slider file should be deleted from storage disk
        Storage::disk('public')->assertMissing($thumbPath);
        Storage::disk('public')->assertMissing($slidePath);

        // Old records in media table should be gone
        $this->assertDatabaseMissing('media', ['id' => $thumbRecord->id]);
        $this->assertDatabaseMissing('media', ['id' => $sliderRecord->id]);

        // New thumbnail and new slider should be in media table
        $newThumbMedia = $product->media()->where('collection', 'thumbnail')->first();
        $this->assertNotNull($newThumbMedia);
        Storage::disk('public')->assertExists($newThumbMedia->path);

        $newSliderMedia = $product->media()->where('collection', 'slider')->first();
        $this->assertNotNull($newSliderMedia);
        Storage::disk('public')->assertExists($newSliderMedia->path);
    }

    public function test_product_create_and_edit_blade_views_render_successfully(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Amoxicillin 250mg',
            'slug' => 'amoxicillin-250mg',
            'kind' => 'medicine',
            'status' => 'active',
        ]);

        $product->variants()->create([
            'tenant_id' => $this->tenant->id,
            'sku' => 'AMOX-250',
            'title' => 'Default',
            'price' => 12.00,
            'is_default' => true,
            'position' => 0,
            'status' => 'active',
            'options_hash' => md5('[]'),
        ]);

        // Test create page render
        $createResponse = $this->actingAs($this->user)->get($this->storeUrl('/store/catalog/products/create'));
        $createResponse->assertOk();
        $createResponse->assertSee('Thumbnail image (Single)');
        $createResponse->assertSee('Slider images (Multiple)');
        $createResponse->assertSee('AVIF');
        $createResponse->assertSee('Search engine optimization (SEO)');

        // Test edit page render
        $editResponse = $this->actingAs($this->user)->get($this->storeUrl('/store/catalog/products/' . $product->id . '/edit'));
        $editResponse->assertOk();
        $editResponse->assertSee('Thumbnail image (Single)');
        $editResponse->assertSee('Slider images (Multiple)');
        $editResponse->assertSee('Amoxicillin 250mg');
    }

    public function test_product_update_with_multiple_variants_without_attributes_does_not_cause_duplicate_options_hash_error(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Multi Variant Drug',
            'slug' => 'multi-variant-drug',
            'kind' => 'general',
            'status' => 'active',
        ]);

        $var1 = $product->variants()->create([
            'tenant_id' => $this->tenant->id,
            'sku' => 'MVD-1',
            'title' => 'Small Box',
            'price' => 10.00,
            'is_default' => true,
            'position' => 0,
            'status' => 'active',
            'options_hash' => md5('v:small-box:MVD-1'),
        ]);

        $var2 = $product->variants()->create([
            'tenant_id' => $this->tenant->id,
            'sku' => 'MVD-2',
            'title' => 'Large Box',
            'price' => 25.00,
            'is_default' => false,
            'position' => 1,
            'status' => 'active',
            'options_hash' => md5('v:large-box:MVD-2'),
        ]);

        // Update both variants (e.g. updating cost_price or price) without selecting attribute values
        $response = $this->actingAs($this->user)
            ->put($this->storeUrl('/store/catalog/products/' . $product->id), [
                'name' => 'Multi Variant Drug Updated',
                'slug' => 'multi-variant-drug',
                'kind' => 'general',
                'status' => 'active',
                'variants' => [
                    [
                        'id' => $var1->id,
                        'title' => 'Small Box',
                        'sku' => 'MVD-1',
                        'price' => 12.00,
                        'cost_price' => 5.00,
                    ],
                    [
                        'id' => $var2->id,
                        'title' => 'Large Box',
                        'sku' => 'MVD-2',
                        'price' => 28.00,
                        'cost_price' => 10.00,
                    ],
                ],
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('store.admin.products.index'));
        $this->assertEquals(10.00, $var2->fresh()->cost_price);
        $this->assertEquals(5.00, $var1->fresh()->cost_price);
    }

    private function storeUrl(string $path): string
    {
        return 'http://' . config('app.base_domain') . $path;
    }
}
