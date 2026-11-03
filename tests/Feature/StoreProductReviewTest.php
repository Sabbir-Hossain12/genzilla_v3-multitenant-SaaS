<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StoreProductReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;
    private User $otherUser;
    private Tenant $otherTenant;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(PermissionSeeder::class);

        // 1. Create main user & tenant
        $this->user = User::create([
            'name' => 'Store Pharmacist Admin',
            'email' => 'pharmacist@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->tenant = Tenant::create([
            'user_id' => $this->user->id,
            'store_name' => 'City Pharma',
            'subdomain' => 'citypharma',
            'subscription_status' => 'active',
            'status' => 'active',
        ]);

        // 2. Setup Spatie Roles & Permissions for main tenant
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->tenant->id);

        $role = Role::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Store Owner',
            'guard_name' => 'web',
        ]);

        $permissions = [
            'Store Dashboard',
            'Store Product Review List',
            'Store Product Review View',
            'Store Product Review Approve',
            'Store Product Review Reply',
            'Store Product Review Delete',
        ];

        foreach ($permissions as $p) {
            $role->givePermissionTo($p);
        }

        $this->user->assignRole($role);

        // 3. Create products for testing
        $this->product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Napa Extra 500mg',
            'slug' => 'napa-extra-500mg',
            'generic_name' => 'Paracetamol + Caffeine',
            'status' => 'active',
            'kind' => 'medicine',
        ]);

        // 4. Create other tenant for multitenant isolation checks
        $this->otherUser = User::create([
            'name' => 'Other Tenant Admin',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->otherTenant = Tenant::create([
            'user_id' => $this->otherUser->id,
            'store_name' => 'Other Pharma',
            'subdomain' => 'otherpharma',
            'subscription_status' => 'active',
            'status' => 'active',
        ]);

        $registrar->setPermissionsTeamId($this->otherTenant->id);
        $otherRole = Role::create([
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Store Owner',
            'guard_name' => 'web',
        ]);
        foreach ($permissions as $p) {
            $otherRole->givePermissionTo($p);
        }
        $this->otherUser->assignRole($otherRole);

        // Reset registrar team id
        $registrar->setPermissionsTeamId($this->tenant->id);
    }

    public function test_merchant_can_view_product_reviews_index_with_kpis(): void
    {
        ProductReview::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'reviewer_name' => 'Rahim Islam',
            'reviewer_email' => 'rahim@example.com',
            'rating' => 5,
            'title' => 'Original medicine',
            'comment' => 'Very fast delivery, genuine product.',
            'status' => 'approved',
            'is_verified_purchase' => true,
        ]);

        ProductReview::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'reviewer_name' => 'Karim Hasan',
            'reviewer_email' => 'karim@example.com',
            'rating' => 4,
            'title' => 'Good packaging',
            'comment' => 'Arrived intact.',
            'status' => 'pending',
            'is_verified_purchase' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('store.admin.reviews.index'));

        $response->assertStatus(200);
        $response->assertSee('Product Reviews & Ratings');
        $response->assertSee('Rahim Islam');
        $response->assertSee('Karim Hasan');
        $response->assertSee('Napa Extra 500mg');
        $response->assertSee('Pending Moderation');
    }

    public function test_merchant_can_filter_reviews_by_status(): void
    {
        ProductReview::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'reviewer_name' => 'Pending Reviewer',
            'reviewer_email' => 'pending@example.com',
            'rating' => 5,
            'comment' => 'Pending comment.',
            'status' => 'pending',
        ]);

        ProductReview::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'reviewer_name' => 'Approved Reviewer',
            'reviewer_email' => 'approved@example.com',
            'rating' => 5,
            'comment' => 'Approved comment.',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('store.admin.reviews.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('Pending Reviewer');
        $response->assertDontSee('Approved Reviewer');
    }

    public function test_merchant_can_filter_reviews_by_rating(): void
    {
        ProductReview::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'reviewer_name' => 'Five Star User',
            'rating' => 5,
            'comment' => 'Five stars comment.',
            'status' => 'approved',
        ]);

        ProductReview::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'reviewer_name' => 'One Star User',
            'rating' => 1,
            'comment' => 'One star comment.',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('store.admin.reviews.index', ['rating' => '5']));

        $response->assertStatus(200);
        $response->assertSee('Five Star User');
        $response->assertDontSee('One Star User');
    }

    public function test_merchant_can_view_review_details_via_ajax(): void
    {
        $review = ProductReview::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'reviewer_name' => 'Ajax Reviewer',
            'reviewer_email' => 'ajax@example.com',
            'rating' => 5,
            'title' => 'Quick healing',
            'comment' => 'Fever reduced quickly.',
            'status' => 'pending',
            'is_verified_purchase' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('store.admin.reviews.show', $review->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'review' => [
                'id' => $review->id,
                'reviewer_name' => 'Ajax Reviewer',
                'title' => 'Quick healing',
                'rating' => 5,
                'is_verified_purchase' => true,
            ],
        ]);
    }

    public function test_merchant_can_update_review_status(): void
    {
        $review = ProductReview::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'reviewer_name' => 'Status Test',
            'rating' => 5,
            'comment' => 'Checking status update.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->patch(route('store.admin.reviews.status', $review->id), [
                'status' => 'approved',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('product_reviews', [
            'id' => $review->id,
            'status' => 'approved',
        ]);
    }

    public function test_merchant_can_reply_to_review(): void
    {
        $review = ProductReview::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'reviewer_name' => 'Patient Faruk',
            'rating' => 4,
            'comment' => 'How often should I take this?',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('store.admin.reviews.reply', $review->id), [
                'admin_reply' => 'Take 1 tablet after meals twice a day or as directed by your physician.',
                'auto_approve' => 1,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('product_reviews', [
            'id' => $review->id,
            'admin_reply' => 'Take 1 tablet after meals twice a day or as directed by your physician.',
            'status' => 'approved',
            'replied_by' => $this->user->id,
        ]);
    }

    public function test_merchant_can_delete_review(): void
    {
        $review = ProductReview::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'reviewer_name' => 'Spammer',
            'rating' => 1,
            'comment' => 'Spam comment',
            'status' => 'rejected',
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('store.admin.reviews.destroy', $review->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('product_reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_merchant_can_perform_bulk_actions(): void
    {
        $r1 = ProductReview::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'reviewer_name' => 'Bulk 1',
            'rating' => 5,
            'comment' => 'Bulk 1 comment',
            'status' => 'pending',
        ]);

        $r2 = ProductReview::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $this->product->id,
            'reviewer_name' => 'Bulk 2',
            'rating' => 4,
            'comment' => 'Bulk 2 comment',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('store.admin.reviews.bulk'), [
                'ids' => [$r1->id, $r2->id],
                'action' => 'approve',
            ]);

        $response->assertRedirect();
        $this->assertEquals('approved', $r1->fresh()->status);
        $this->assertEquals('approved', $r2->fresh()->status);
    }

    public function test_multitenant_isolation_protects_reviews(): void
    {
        $otherProduct = Product::create([
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Other Medicine',
            'slug' => 'other-medicine',
            'status' => 'active',
            'kind' => 'medicine',
        ]);

        $otherReview = ProductReview::create([
            'tenant_id' => $this->otherTenant->id,
            'product_id' => $otherProduct->id,
            'reviewer_name' => 'Confidential Reviewer',
            'rating' => 5,
            'comment' => 'Confidential patient health detail.',
            'status' => 'approved',
        ]);

        // User from tenant A should not see other tenant's review in index
        $indexResponse = $this->actingAs($this->user)
            ->get(route('store.admin.reviews.index'));
        $indexResponse->assertDontSee('Confidential Reviewer');

        // User from tenant A should be 404 forbidden from viewing or modifying other review
        $showResponse = $this->actingAs($this->user)
            ->get(route('store.admin.reviews.show', $otherReview->id));
        $showResponse->assertStatus(404);

        $statusResponse = $this->actingAs($this->user)
            ->patch(route('store.admin.reviews.status', $otherReview->id), [
                'status' => 'rejected',
            ]);
        $statusResponse->assertStatus(404);
    }
}
