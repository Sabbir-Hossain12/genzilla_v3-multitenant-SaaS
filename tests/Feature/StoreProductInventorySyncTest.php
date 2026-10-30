<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StoreProductInventorySyncTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Store Owner User',
            'email' => 'storeowner@example.com',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $this->tenant = Tenant::create([
            'user_id' => $this->user->id,
            'store_name' => 'Sync Test Store',
            'subdomain' => 'synctest',
            'subscription_status' => 'active',
            'status' => 'active',
        ]);

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->tenant->id);

        $role = Role::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Store Manager',
            'guard_name' => 'web',
        ]);

        $permissions = [
            'Store Product List',
            'Store Product Create',
            'Store Product Edit',
            'Store Product Delete',
            'Store Inventory List',
            'Store Inventory Manage',
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

    public function test_single_variant_creation_syncs_with_inventory_batch_and_stock_movement(): void
    {
        $response = $this->actingAs($this->user)->post($this->storeUrl('/store/catalog/products'), [
            'name' => 'Paracetamol 500mg Tablets',
            'slug' => 'paracetamol-500mg-tablets',
            'kind' => 'medicine',
            'status' => 'active',
            'sku' => 'PARA-500-TAB',
            'price' => 10.00,
            'cost_price' => 4.50,
            'initial_quantity' => 150,
            'track_inventory' => 1,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('store.admin.products.index'));

        $product = Product::where('tenant_id', $this->tenant->id)->where('slug', 'paracetamol-500mg-tablets')->first();
        $this->assertNotNull($product);

        $variant = $product->variants()->first();
        $this->assertNotNull($variant);
        $this->assertEquals('PARA-500-TAB', $variant->sku);
        $this->assertTrue($variant->track_inventory);

        // Verify InventoryBatch was created
        $batch = InventoryBatch::where('tenant_id', $this->tenant->id)->where('variant_id', $variant->id)->first();
        $this->assertNotNull($batch);
        $this->assertEquals('DEFAULT', $batch->batch_no);
        $this->assertEquals(150, $batch->quantity);
        $this->assertEquals(0, $batch->reserved);
        $this->assertEquals(4.50, $batch->cost_price);
        $this->assertEquals('active', $batch->status);

        // Verify StockMovement audit entry
        $movement = StockMovement::where('tenant_id', $this->tenant->id)->where('variant_id', $variant->id)->first();
        $this->assertNotNull($movement);
        $this->assertEquals($batch->id, $movement->batch_id);
        $this->assertEquals('receive', $movement->type);
        $this->assertEquals(150, $movement->qty_delta);
        $this->assertEquals(0, $movement->reserved_delta);
        $this->assertEquals($this->user->id, $movement->user_id);
        $this->assertEquals('Opening stock', $movement->note);
        $this->assertNotNull($movement->created_at);

        // Verify model accessors are in sync
        $this->assertEquals(150, $variant->stock_on_hand);
        $this->assertEquals(150, $variant->available_stock);
        $this->assertTrue($variant->is_in_stock);
        $this->assertEquals(150, $product->total_stock);
        $this->assertEquals(150, $product->available_stock);
    }

    public function test_multi_variant_creation_syncs_independent_batches_and_aggregates_total_stock(): void
    {
        $response = $this->actingAs($this->user)->post($this->storeUrl('/store/catalog/products'), [
            'name' => 'Cotton T-Shirt',
            'slug' => 'cotton-t-shirt',
            'kind' => 'general',
            'status' => 'active',
            'default_variant_index' => 0,
            'variants' => [
                [
                    'title' => 'Small',
                    'sku' => 'TSHIRT-S',
                    'price' => 15.00,
                    'cost_price' => 7.00,
                    'initial_quantity' => 25,
                    'track_inventory' => 1,
                ],
                [
                    'title' => 'Medium',
                    'sku' => 'TSHIRT-M',
                    'price' => 18.00,
                    'cost_price' => 8.00,
                    'initial_quantity' => 40,
                    'track_inventory' => 1,
                ],
                [
                    'title' => 'Large',
                    'sku' => 'TSHIRT-L',
                    'price' => 20.00,
                    'cost_price' => 9.00,
                    'initial_quantity' => 35,
                    'track_inventory' => 1,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $product = Product::where('tenant_id', $this->tenant->id)->where('slug', 'cotton-t-shirt')->first();
        $this->assertNotNull($product);

        $this->assertEquals(3, $product->variants()->count());
        $this->assertEquals(100, $product->total_stock); // 25 + 40 + 35
        $this->assertEquals(100, $product->available_stock);
        $this->assertEquals(15.00, $product->min_price);
        $this->assertEquals(20.00, $product->max_price);

        // Verify Products index page displays price range and total stock
        $indexResponse = $this->actingAs($this->user)->get($this->storeUrl('/store/catalog/products'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Cotton T-Shirt');
        $indexResponse->assertSee('15.00 - 20.00');
        $indexResponse->assertSee('100');
        $indexResponse->assertSee('(3 variants)');
    }

    public function test_receiving_stock_via_inventory_controller(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Amoxicillin 500mg',
            'slug' => 'amoxicillin-500mg',
            'kind' => 'medicine',
            'status' => 'active',
        ]);

        $variant = $product->variants()->create([
            'tenant_id' => $this->tenant->id,
            'sku' => 'AMOX-500',
            'title' => 'Standard',
            'price' => 12.00,
            'track_inventory' => true,
            'is_default' => true,
            'position' => 0,
            'status' => 'active',
            'options_hash' => md5('default'),
        ]);

        // Receive new stock into a new specific batch
        $response = $this->actingAs($this->user)->post($this->storeUrl('/store/catalog/inventory/' . $variant->id . '/receive'), [
            'batch_no' => 'LOT-2026-A1',
            'quantity' => 200,
            'expiry_date' => '2028-12-31',
            'cost_price' => 5.20,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $batch = InventoryBatch::where('tenant_id', $this->tenant->id)->where('variant_id', $variant->id)->where('batch_no', 'LOT-2026-A1')->first();
        $this->assertNotNull($batch);
        $this->assertEquals(200, $batch->quantity);
        $this->assertEquals('2028-12-31', $batch->expiry_date->format('Y-m-d'));
        $this->assertEquals(5.20, $batch->cost_price);

        // Movement logged
        $movement = StockMovement::where('tenant_id', $this->tenant->id)->where('batch_id', $batch->id)->first();
        $this->assertNotNull($movement);
        $this->assertEquals('receive', $movement->type);
        $this->assertEquals(200, $movement->qty_delta);
        $this->assertEquals($this->user->id, $movement->user_id);

        // Second receive into same batch increments quantity
        $response2 = $this->actingAs($this->user)->post($this->storeUrl('/store/catalog/inventory/' . $variant->id . '/receive'), [
            'batch_no' => 'LOT-2026-A1',
            'quantity' => 50,
            'cost_price' => 5.20,
        ]);
        $response2->assertSessionHasNoErrors();

        $this->assertEquals(250, $batch->fresh()->quantity);
        $this->assertEquals(250, $variant->fresh()->stock_on_hand);
        $this->assertEquals(250, $product->fresh()->total_stock);
    }

    public function test_adjusting_stock_and_status_via_inventory_controller(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Ibuprofen 400mg',
            'slug' => 'ibuprofen-400mg',
            'kind' => 'medicine',
            'status' => 'active',
        ]);

        $variant = $product->variants()->create([
            'tenant_id' => $this->tenant->id,
            'sku' => 'IBU-400',
            'title' => 'Default',
            'price' => 8.00,
            'track_inventory' => true,
            'is_default' => true,
            'position' => 0,
            'status' => 'active',
            'options_hash' => md5('default'),
        ]);

        $batch = InventoryBatch::create([
            'tenant_id' => $this->tenant->id,
            'variant_id' => $variant->id,
            'batch_no' => 'DEFAULT',
            'quantity' => 100,
            'reserved' => 10,
            'status' => 'active',
        ]);

        // 1. Positive adjustment
        $res1 = $this->actingAs($this->user)->patch($this->storeUrl('/store/catalog/inventory/batches/' . $batch->id), [
            'quantity_delta' => 15,
            'note' => 'Found uncounted box',
        ]);
        $res1->assertSessionHasNoErrors();
        $this->assertEquals(115, $batch->fresh()->quantity);

        // 2. Negative adjustment within allowed threshold
        $res2 = $this->actingAs($this->user)->patch($this->storeUrl('/store/catalog/inventory/batches/' . $batch->id), [
            'quantity_delta' => -20,
            'note' => 'Damaged during inspection',
        ]);
        $res2->assertSessionHasNoErrors();
        $this->assertEquals(95, $batch->fresh()->quantity);

        // 3. Attempt reduction below reserved quantity (reserved is 10, reducing by 90 leaves 5 < 10)
        $res3 = $this->actingAs($this->user)->patch($this->storeUrl('/store/catalog/inventory/batches/' . $batch->id), [
            'quantity_delta' => -90,
            'note' => 'Excess reduction',
        ]);
        $res3->assertSessionHasErrors(['quantity_delta']);
        $this->assertEquals(95, $batch->fresh()->quantity); // unchanged

        // 4. Change status to quarantined
        $res4 = $this->actingAs($this->user)->patch($this->storeUrl('/store/catalog/inventory/batches/' . $batch->id), [
            'status' => 'quarantined',
            'note' => 'Under regulatory review',
        ]);
        $res4->assertSessionHasNoErrors();
        $this->assertEquals('quarantined', $batch->fresh()->status);

        // Quarantined stock is on-hand but NOT available for sale
        $this->assertEquals(95, $variant->fresh()->stock_on_hand);
        $this->assertEquals(0, $variant->fresh()->available_stock);
        $this->assertEquals(0, $product->fresh()->available_stock);
    }

    public function test_archived_variant_is_excluded_from_active_inventory_and_product_stock(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Energy Drink',
            'slug' => 'energy-drink',
            'kind' => 'general',
            'status' => 'active',
        ]);

        $varActive = $product->variants()->create([
            'tenant_id' => $this->tenant->id,
            'sku' => 'ED-CAN',
            'title' => '250ml Can',
            'price' => 3.00,
            'track_inventory' => true,
            'is_default' => true,
            'position' => 0,
            'status' => 'active',
            'options_hash' => md5('v:can:ED-CAN'),
        ]);

        $varArchived = $product->variants()->create([
            'tenant_id' => $this->tenant->id,
            'sku' => 'ED-BOTTLE',
            'title' => '500ml Bottle Discontinued',
            'price' => 5.00,
            'track_inventory' => true,
            'is_default' => false,
            'position' => 1,
            'status' => 'archived',
            'options_hash' => md5('archived_bottle'),
        ]);

        InventoryBatch::create([
            'tenant_id' => $this->tenant->id,
            'variant_id' => $varActive->id,
            'batch_no' => 'DEFAULT',
            'quantity' => 50,
            'status' => 'active',
        ]);

        InventoryBatch::create([
            'tenant_id' => $this->tenant->id,
            'variant_id' => $varArchived->id,
            'batch_no' => 'DEFAULT',
            'quantity' => 80,
            'status' => 'active',
        ]);

        // Product total stock must only reflect active variants (50, not 130)
        $this->assertEquals(50, $product->fresh()->total_stock);
        $this->assertEquals(50, $product->fresh()->available_stock);

        // Inventory page must NOT list the discontinued archived variant
        $invResponse = $this->actingAs($this->user)->get($this->storeUrl('/store/catalog/inventory'));
        $invResponse->assertOk();
        $invResponse->assertSee('250ml Can');
        $invResponse->assertDontSee('500ml Bottle Discontinued');

        // Receiving stock on archived variant is disallowed
        $receiveResponse = $this->actingAs($this->user)->post($this->storeUrl('/store/catalog/inventory/' . $varArchived->id . '/receive'), [
            'batch_no' => 'DEFAULT',
            'quantity' => 10,
        ]);
        $receiveResponse->assertNotFound();
    }

    public function test_product_deletion_soft_deletes_and_cleans_inventory_view(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Temporary Promo Product',
            'slug' => 'temporary-promo-product',
            'kind' => 'general',
            'status' => 'active',
        ]);

        $variant = $product->variants()->create([
            'tenant_id' => $this->tenant->id,
            'sku' => 'PROMO-1',
            'title' => 'Default',
            'price' => 1.00,
            'track_inventory' => true,
            'is_default' => true,
            'position' => 0,
            'status' => 'active',
            'options_hash' => md5('default'),
        ]);

        $batch = InventoryBatch::create([
            'tenant_id' => $this->tenant->id,
            'variant_id' => $variant->id,
            'batch_no' => 'DEFAULT',
            'quantity' => 30,
            'status' => 'active',
        ]);

        StockMovement::create([
            'tenant_id' => $this->tenant->id,
            'variant_id' => $variant->id,
            'batch_id' => $batch->id,
            'type' => 'receive',
            'qty_delta' => 30,
            'note' => 'Promo stock',
            'created_at' => now(),
        ]);

        // Archive/delete product
        $delResponse = $this->actingAs($this->user)->delete($this->storeUrl('/store/catalog/products/' . $product->id));
        $delResponse->assertRedirect();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertEquals('archived', $variant->fresh()->status);

        // Inventory page does not list deleted product
        $invResponse = $this->actingAs($this->user)->get($this->storeUrl('/store/catalog/inventory'));
        $invResponse->assertOk();
        $invResponse->assertDontSee('Temporary Promo Product - Default');

        // But movement history remains visible for auditing
        $invResponse->assertSee('Promo stock');
    }

    public function test_untracked_inventory_variant_is_considered_always_in_stock(): void
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Consultation Service',
            'slug' => 'consultation-service',
            'kind' => 'general',
            'status' => 'active',
        ]);

        $variant = $product->variants()->create([
            'tenant_id' => $this->tenant->id,
            'sku' => 'SERVICE-CONSULT',
            'title' => '1 Hour Session',
            'price' => 50.00,
            'track_inventory' => false,
            'is_default' => true,
            'position' => 0,
            'status' => 'active',
            'options_hash' => md5('default'),
        ]);

        // Has 0 batches, but track_inventory is false
        $this->assertEquals(0, $variant->stock_on_hand);
        $this->assertTrue($variant->is_in_stock);
        $this->assertFalse($product->tracks_inventory);

        $indexResponse = $this->actingAs($this->user)->get($this->storeUrl('/store/catalog/products'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Untracked');
    }

    private function storeUrl(string $path): string
    {
        return 'http://' . config('app.base_domain') . $path;
    }
}
