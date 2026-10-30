<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Media;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StoreCategoryBrandTest extends TestCase
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
            'Store Category List',
            'Store Category Create',
            'Store Category Edit',
            'Store Category Delete',
            'Store Brand List',
            'Store Brand Create',
            'Store Brand Edit',
            'Store Brand Delete',
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

    public function test_category_can_be_created_with_image_and_seo_fields(): void
    {
        $image = UploadedFile::fake()->image('vitamins.jpg', 400, 400);

        $response = $this->actingAs($this->user)
            ->post($this->storeUrl('/store/catalog/categories'), [
                'name' => 'Vitamins & Supplements',
                'slug' => 'vitamins-supplements',
                'position' => 1,
                'status' => 1,
                'image' => $image,
                'meta_title' => 'Best Vitamins Online',
                'meta_description' => 'Browse our health vitamins catalog.',
                'h1_heading' => 'High Quality Vitamins & Supplements',
                'canonical_url' => 'https://teststore.com/categories/vitamins-supplements',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $category = Category::where('tenant_id', $this->tenant->id)->where('slug', 'vitamins-supplements')->first();
        $this->assertNotNull($category);
        $this->assertNotNull($category->image);
        Storage::disk('public')->assertExists($category->image);

        $this->assertDatabaseHas('media', [
            'tenant_id' => $this->tenant->id,
            'model_type' => Category::class,
            'model_id' => $category->id,
            'collection' => 'cover',
            'path' => $category->image,
            'alt' => 'Vitamins & Supplements',
        ]);

        $this->assertEquals('Best Vitamins Online', $category->meta_title);
        $this->assertEquals('Browse our health vitamins catalog.', $category->meta_description);
        $this->assertEquals('High Quality Vitamins & Supplements', $category->h1_heading);
        $this->assertEquals('https://teststore.com/categories/vitamins-supplements', $category->canonical_url);
    }

    public function test_category_can_be_updated_with_new_image_and_seo_fields(): void
    {
        $oldImage = UploadedFile::fake()->image('old_cat.jpg');
        $oldPath = $oldImage->store('tenants/' . $this->tenant->id . '/categories', 'public');

        $category = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Herbal Remedies',
            'slug' => 'herbal-remedies',
            'image' => $oldPath,
            'status' => true,
            'position' => 0,
            'meta_title' => 'Old Title',
            'meta_description' => 'Old Desc',
            'h1_heading' => 'Old H1',
            'canonical_url' => 'https://teststore.com/old',
        ]);
        $category->media()->create([
            'tenant_id' => $this->tenant->id,
            'collection' => 'cover',
            'path' => $oldPath,
            'mime' => 'image/jpeg',
            'size_bytes' => 1024,
            'position' => 0,
            'alt' => 'Old Title',
            'created_at' => now(),
        ]);

        Storage::disk('public')->assertExists($oldPath);

        $newImage = UploadedFile::fake()->image('new_cat.png');

        $response = $this->actingAs($this->user)
            ->put($this->storeUrl('/store/catalog/categories/' . $category->id), [
                'name' => 'Organic Herbal Remedies',
                'slug' => 'organic-herbal-remedies',
                'position' => 2,
                'status' => 1,
                'image' => $newImage,
                'meta_title' => 'Updated Meta Title',
                'meta_description' => 'Updated Meta Description',
                'h1_heading' => 'Updated H1 Heading',
                'canonical_url' => 'https://teststore.com/categories/organic-herbal',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $category->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($category->image);

        $this->assertDatabaseMissing('media', [
            'path' => $oldPath,
        ]);
        $this->assertDatabaseHas('media', [
            'tenant_id' => $this->tenant->id,
            'model_type' => Category::class,
            'model_id' => $category->id,
            'collection' => 'cover',
            'path' => $category->image,
            'alt' => 'Organic Herbal Remedies',
        ]);

        $this->assertEquals('Organic Herbal Remedies', $category->name);
        $this->assertEquals('Updated Meta Title', $category->meta_title);
        $this->assertEquals('Updated Meta Description', $category->meta_description);
        $this->assertEquals('Updated H1 Heading', $category->h1_heading);
        $this->assertEquals('https://teststore.com/categories/organic-herbal', $category->canonical_url);
    }

    public function test_category_can_be_deleted_along_with_stored_image(): void
    {
        $image = UploadedFile::fake()->image('to_delete.jpg');
        $path = $image->store('tenants/' . $this->tenant->id . '/categories', 'public');

        $category = Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'To Delete',
            'slug' => 'to-delete',
            'image' => $path,
            'status' => true,
        ]);
        $category->media()->create([
            'tenant_id' => $this->tenant->id,
            'collection' => 'cover',
            'path' => $path,
            'mime' => 'image/jpeg',
            'size_bytes' => 1024,
            'position' => 0,
            'alt' => 'To Delete',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->delete($this->storeUrl('/store/catalog/categories/' . $category->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertDatabaseMissing('media', ['model_type' => Category::class, 'model_id' => $category->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_brand_can_be_created_with_image_and_seo_fields(): void
    {
        $image = UploadedFile::fake()->image('brand_logo.png', 200, 200);

        $response = $this->actingAs($this->user)
            ->post($this->storeUrl('/store/catalog/brands'), [
                'name' => 'Pfizer Care',
                'slug' => 'pfizer-care',
                'status' => 1,
                'image' => $image,
                'meta_title' => 'Pfizer Health Products',
                'meta_description' => 'Official Pfizer products store.',
                'h1_heading' => 'Pfizer Care Brand Store',
                'canonical_url' => 'https://teststore.com/brands/pfizer-care',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $brand = Brand::where('tenant_id', $this->tenant->id)->where('slug', 'pfizer-care')->first();
        $this->assertNotNull($brand);
        $this->assertNotNull($brand->image);
        Storage::disk('public')->assertExists($brand->image);

        $this->assertDatabaseHas('media', [
            'tenant_id' => $this->tenant->id,
            'model_type' => Brand::class,
            'model_id' => $brand->id,
            'collection' => 'cover',
            'path' => $brand->image,
            'alt' => 'Pfizer Care',
        ]);

        $this->assertEquals('Pfizer Health Products', $brand->meta_title);
        $this->assertEquals('Official Pfizer products store.', $brand->meta_description);
        $this->assertEquals('Pfizer Care Brand Store', $brand->h1_heading);
        $this->assertEquals('https://teststore.com/brands/pfizer-care', $brand->canonical_url);
    }

    public function test_brand_can_be_updated_with_new_image_and_seo_fields(): void
    {
        $oldImage = UploadedFile::fake()->image('old_brand.jpg');
        $oldPath = $oldImage->store('tenants/' . $this->tenant->id . '/brands', 'public');

        $brand = Brand::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Novartis',
            'slug' => 'novartis',
            'image' => $oldPath,
            'status' => true,
            'meta_title' => 'Novartis Old',
            'meta_description' => 'Novartis Desc',
            'h1_heading' => 'Novartis H1',
            'canonical_url' => 'https://teststore.com/brands/old-novartis',
        ]);
        $brand->media()->create([
            'tenant_id' => $this->tenant->id,
            'collection' => 'cover',
            'path' => $oldPath,
            'mime' => 'image/jpeg',
            'size_bytes' => 1024,
            'position' => 0,
            'alt' => 'Novartis Old',
            'created_at' => now(),
        ]);

        $newImage = UploadedFile::fake()->image('new_brand.png');

        $response = $this->actingAs($this->user)
            ->put($this->storeUrl('/store/catalog/brands/' . $brand->id), [
                'name' => 'Novartis Pharma',
                'slug' => 'novartis-pharma',
                'status' => 1,
                'image' => $newImage,
                'meta_title' => 'Novartis Pharma Products',
                'meta_description' => 'Buy Novartis medicines online.',
                'h1_heading' => 'Novartis Pharmaceuticals',
                'canonical_url' => 'https://teststore.com/brands/novartis-pharma',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $brand->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($brand->image);

        $this->assertDatabaseMissing('media', [
            'path' => $oldPath,
        ]);
        $this->assertDatabaseHas('media', [
            'tenant_id' => $this->tenant->id,
            'model_type' => Brand::class,
            'model_id' => $brand->id,
            'collection' => 'cover',
            'path' => $brand->image,
            'alt' => 'Novartis Pharma',
        ]);

        $this->assertEquals('Novartis Pharma', $brand->name);
        $this->assertEquals('Novartis Pharma Products', $brand->meta_title);
        $this->assertEquals('Buy Novartis medicines online.', $brand->meta_description);
        $this->assertEquals('Novartis Pharmaceuticals', $brand->h1_heading);
        $this->assertEquals('https://teststore.com/brands/novartis-pharma', $brand->canonical_url);
    }

    public function test_brand_can_be_deleted_along_with_stored_image(): void
    {
        $image = UploadedFile::fake()->image('brand_del.jpg');
        $path = $image->store('tenants/' . $this->tenant->id . '/brands', 'public');

        $brand = Brand::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Brand To Delete',
            'slug' => 'brand-to-delete',
            'image' => $path,
            'status' => true,
        ]);
        $brand->media()->create([
            'tenant_id' => $this->tenant->id,
            'collection' => 'cover',
            'path' => $path,
            'mime' => 'image/jpeg',
            'size_bytes' => 1024,
            'position' => 0,
            'alt' => 'Brand To Delete',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->delete($this->storeUrl('/store/catalog/brands/' . $brand->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
        $this->assertDatabaseMissing('media', ['model_type' => Brand::class, 'model_id' => $brand->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_categories_and_brands_blade_views_render_successfully(): void
    {
        Category::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Antibiotics',
            'slug' => 'antibiotics',
            'meta_title' => 'Antibiotics Online',
            'h1_heading' => 'Prescription Antibiotics',
            'status' => true,
        ]);

        Brand::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bayer',
            'slug' => 'bayer',
            'meta_title' => 'Bayer Healthcare',
            'h1_heading' => 'Bayer Consumer Health',
            'status' => true,
        ]);

        $catResponse = $this->actingAs($this->user)->get($this->storeUrl('/store/catalog/categories'));
        $catResponse->assertOk();
        $catResponse->assertSee('Antibiotics');
        $catResponse->assertSee('SEO configured');

        $brandResponse = $this->actingAs($this->user)->get($this->storeUrl('/store/catalog/brands'));
        $brandResponse->assertOk();
        $brandResponse->assertSee('Bayer');
        $brandResponse->assertSee('SEO configured');
    }

    private function storeUrl(string $path): string
    {
        return 'http://' . config('app.base_domain') . $path;
    }
}
