<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Coupons
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code', 50);
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('value', 12, 2);
            $table->decimal('min_order_amount', 12, 2)->nullable();
            $table->decimal('max_discount_amount', 12, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_customer')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'is_active', 'expires_at']);
        });

        // 2. Delivery Charges
        Schema::create('delivery_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            $table->string('slug', 160);
            $table->text('description')->nullable();
            $table->decimal('charge_amount', 12, 2);
            $table->decimal('min_order_amount', 12, 2)->nullable();
            $table->string('estimated_days', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'status', 'sort_order']);
        });

        // 3. Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('public_id', 36)->unique();
            $table->string('order_number', 50);
            $table->string('idempotency_key', 64)->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('pending');
            $table->string('payment_method', 50)->default('cod');
            $table->string('payment_status', 30)->default('unpaid');
            $table->string('prescription_status', 30)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('shipping_charge', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->string('coupon_code', 50)->nullable();
            $table->foreignId('delivery_charge_id')->nullable()->constrained('delivery_charges')->nullOnDelete();
            $table->string('shipping_method', 100)->nullable();
            $table->string('shipping_name', 150);
            $table->string('shipping_phone', 50);
            $table->string('shipping_email', 150)->nullable();
            $table->text('shipping_address');
            $table->string('shipping_area', 100)->nullable();
            $table->string('shipping_city', 100);
            $table->string('shipping_postcode', 30)->nullable();
            $table->string('shipping_country', 100)->default('Bangladesh');
            $table->text('order_note')->nullable();
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('placed_at')->useCurrent();
            $table->timestamp('payment_expires_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'order_number']);
            $table->unique(['tenant_id', 'idempotency_key']);
            $table->index(['tenant_id', 'status', 'created_at']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'payment_status']);
        });

        // 4. Order Products
        Schema::create('order_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('product_name', 200);
            $table->string('variant_title', 150);
            $table->string('sku', 100);
            $table->json('variant_options')->nullable();
            $table->boolean('requires_prescription')->default(false);
            $table->decimal('mrp', 12, 2)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2);
            $table->unsignedInteger('returned_quantity')->default(0);
            $table->timestamps();

            $table->index('order_id');
            $table->index(['tenant_id', 'variant_id']);
        });

        // 5. Order Payments
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('type', 30)->default('charge'); // charge, refund
            $table->foreignId('parent_id')->nullable()->constrained('order_payments')->nullOnDelete();
            $table->string('payment_method', 50);
            $table->string('transaction_id', 150)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('USD');
            $table->string('status', 30)->default('pending'); // pending, successful, failed, refunded
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'payment_method', 'transaction_id']);
            $table->index(['tenant_id', 'order_id']);
            $table->index(['tenant_id', 'status']);
        });

        // 6. Wishlists
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'customer_id', 'product_id', 'product_variant_id'], 'wishlist_tenant_user_prod_var_unique');
            $table->index(['tenant_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('order_payments');
        Schema::dropIfExists('order_products');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('delivery_charges');
        Schema::dropIfExists('coupons');
    }
};
