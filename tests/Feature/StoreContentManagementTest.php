<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Slider;
use App\Models\Tenant;
use App\Models\TenantPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StoreContentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Store Content Admin',
            'email' => 'content_admin@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->tenant = Tenant::create([
            'user_id' => $this->user->id,
            'store_name' => 'Content Test Store',
            'subdomain' => 'contenttest',
            'subscription_status' => 'active',
            'status' => 'active',
        ]);

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->tenant->id);

        $role = Role::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Store Content Manager',
            'guard_name' => 'web',
        ]);

        $permissions = [
            'Store Dashboard',
            'Store Slider List',
            'Store Slider Create',
            'Store Slider Edit',
            'Store Slider Delete',
            'Store Banner List',
            'Store Banner Create',
            'Store Banner Edit',
            'Store Banner Delete',
            'Store Page List',
            'Store Page Create',
            'Store Page Edit',
            'Store Page Delete',
        ];

        foreach ($permissions as $permName) {
            $perm = Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web'],
                ['group_name' => 'Store Content Management']
            );
            $role->givePermissionTo($perm);
        }

        $this->user->assignRole($role);
    }

    private function actingAsContentMerchant(): self
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->tenant->id);

        return $this->actingAs($this->user)->withSession([
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_pages_index_screen_can_be_rendered(): void
    {
        TenantPage::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Terms of Service',
            'slug' => 'terms-of-service',
            'content' => '<p>Terms and conditions content</p>',
            'status' => true,
        ]);

        $response = $this->actingAsContentMerchant()
            ->get(route('store.admin.pages.index'));

        $response->assertStatus(200);
        $response->assertSee('Terms of Service');
        $response->assertSee('/page/terms-of-service');
    }

    public function test_page_can_be_created_with_auto_generated_slug(): void
    {
        $response = $this->actingAsContentMerchant()
            ->post(route('store.admin.pages.store'), [
                'title' => 'Privacy & Security Policy',
                'slug' => '',
                'content' => '<p>Your privacy is important to us.</p>',
                'meta_title' => 'Privacy Policy - Store',
                'meta_description' => 'Official privacy policy for customers',
                'status' => '1',
            ]);

        $response->assertRedirect(route('store.admin.pages.index'));

        $this->assertDatabaseHas('tenant_pages', [
            'tenant_id' => $this->tenant->id,
            'title' => 'Privacy & Security Policy',
            'slug' => 'privacy-security-policy',
            'meta_title' => 'Privacy Policy - Store',
            'status' => 1,
        ]);
    }

    public function test_page_can_be_updated_and_status_toggled(): void
    {
        $page = TenantPage::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Old Title',
            'slug' => 'old-title',
            'content' => '<p>Old content</p>',
            'status' => true,
        ]);

        $updateResponse = $this->actingAsContentMerchant()
            ->put(route('store.admin.pages.update', $page), [
                'title' => 'Updated Policy Title',
                'slug' => 'updated-policy-title',
                'content' => '<p>Updated content</p>',
                'status' => '1',
            ]);

        $updateResponse->assertRedirect(route('store.admin.pages.index'));

        $this->assertDatabaseHas('tenant_pages', [
            'id' => $page->id,
            'title' => 'Updated Policy Title',
            'slug' => 'updated-policy-title',
        ]);

        // Toggle status
        $toggleResponse = $this->actingAsContentMerchant()
            ->patch(route('store.admin.pages.toggle', $page));

        $toggleResponse->assertRedirect(route('store.admin.pages.index'));
        $this->assertDatabaseHas('tenant_pages', [
            'id' => $page->id,
            'status' => 0,
        ]);
    }

    public function test_page_can_be_deleted(): void
    {
        $page = TenantPage::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'To Be Deleted',
            'slug' => 'to-be-deleted',
            'content' => '<p>Delete me</p>',
            'status' => true,
        ]);

        $response = $this->actingAsContentMerchant()
            ->delete(route('store.admin.pages.destroy', $page));

        $response->assertRedirect(route('store.admin.pages.index'));
        $this->assertDatabaseMissing('tenant_pages', ['id' => $page->id]);
    }

    public function test_slider_can_be_created_and_displayed(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('slide1.webp', 1200, 400);

        $response = $this->actingAsContentMerchant()
            ->post(route('store.admin.sliders.store'), [
                'title' => 'Big Discount Sale',
                'subtitle' => 'Save up to 50% this weekend',
                'link' => '/sale',
                'button_text' => 'Shop Now',
                'sort_order' => 1,
                'status' => '1',
                'image' => $image,
            ]);

        $response->assertRedirect(route('store.admin.sliders.index'));

        $this->assertDatabaseHas('sliders', [
            'tenant_id' => $this->tenant->id,
            'title' => 'Big Discount Sale',
            'button_text' => 'Shop Now',
            'sort_order' => 1,
            'status' => 1,
        ]);

        $slider = Slider::where('title', 'Big Discount Sale')->first();
        $this->assertNotNull($slider);
        $this->assertNotNull($slider->image_url);
    }

    public function test_slider_status_can_be_toggled(): void
    {
        $slider = Slider::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Active Slider',
            'image' => 'https://example.com/banner.jpg',
            'status' => true,
            'sort_order' => 0,
        ]);

        $response = $this->actingAsContentMerchant()
            ->patch(route('store.admin.sliders.toggle', $slider));

        $response->assertRedirect(route('store.admin.sliders.index'));
        $this->assertDatabaseHas('sliders', [
            'id' => $slider->id,
            'status' => 0,
        ]);
    }

    public function test_slider_can_be_deleted(): void
    {
        $slider = Slider::create([
            'tenant_id' => $this->tenant->id,
            'title' => 'Slider To Delete',
            'image' => 'https://example.com/slide.jpg',
            'status' => true,
        ]);

        $response = $this->actingAsContentMerchant()
            ->delete(route('store.admin.sliders.destroy', $slider));

        $response->assertRedirect(route('store.admin.sliders.index'));
        $this->assertDatabaseMissing('sliders', ['id' => $slider->id]);
    }

    public function test_banner_can_be_created_with_custom_theme(): void
    {
        $response = $this->actingAsContentMerchant()
            ->post(route('store.admin.banners.store'), [
                'name' => 'Lab Tests Offer',
                'position' => 'promo_grid',
                'eyebrow' => '60% OFF',
                'title' => 'Comprehensive Health Checkup',
                'subtitle' => 'Complete body package with free home sample pickup.',
                'cta_text' => 'Book Test',
                'cta_color' => 'teal',
                'gradient' => 'from-teal-900/90 to-slate-900',
                'link' => '/tests',
                'image_url' => 'https://example.com/lab-test.jpg',
                'status' => '1',
            ]);

        $response->assertRedirect(route('store.admin.banners.index'));

        $this->assertDatabaseHas('banners', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Lab Tests Offer',
            'position' => 'promo_grid',
            'cta_color' => 'teal',
            'status' => 1,
        ]);
    }

    public function test_banner_status_toggle_and_deletion(): void
    {
        $banner = Banner::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Mobile App Banner',
            'position' => 'app_download',
            'title' => 'Download Our App',
            'status' => true,
        ]);

        // Toggle
        $response = $this->actingAsContentMerchant()
            ->patch(route('store.admin.banners.toggle', $banner));

        $response->assertRedirect(route('store.admin.banners.index'));
        $this->assertDatabaseHas('banners', [
            'id' => $banner->id,
            'status' => 0,
        ]);

        // Delete
        $delResponse = $this->actingAsContentMerchant()
            ->delete(route('store.admin.banners.destroy', $banner));

        $delResponse->assertRedirect(route('store.admin.banners.index'));
        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
    }

    public function test_multitenant_isolation_prevents_accessing_other_tenant_content(): void
    {
        $otherUser = User::create([
            'name' => 'Other Merchant',
            'email' => 'other_merchant@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $otherTenant = Tenant::create([
            'user_id' => $otherUser->id,
            'store_name' => 'Competitor Store',
            'subdomain' => 'competitor',
            'subscription_status' => 'active',
            'status' => 'active',
        ]);

        $otherPage = TenantPage::create([
            'tenant_id' => $otherTenant->id,
            'title' => 'Competitor Secret Page',
            'slug' => 'competitor-page',
            'content' => '<p>Secret</p>',
            'status' => true,
        ]);

        $otherSlider = Slider::create([
            'tenant_id' => $otherTenant->id,
            'title' => 'Competitor Slider',
            'status' => true,
        ]);

        $otherBanner = Banner::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Competitor Banner',
            'title' => 'Competitor Promo',
            'status' => true,
        ]);

        // Trying to edit or delete competitor's page
        $this->actingAsContentMerchant()
            ->get(route('store.admin.pages.edit', $otherPage))
            ->assertStatus(404);

        $this->actingAsContentMerchant()
            ->delete(route('store.admin.pages.destroy', $otherPage))
            ->assertStatus(404);

        // Trying to delete competitor's slider
        $this->actingAsContentMerchant()
            ->delete(route('store.admin.sliders.destroy', $otherSlider))
            ->assertStatus(404);

        // Trying to delete competitor's banner
        $this->actingAsContentMerchant()
            ->delete(route('store.admin.banners.destroy', $otherBanner))
            ->assertStatus(404);
    }
}
