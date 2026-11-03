<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Carts Table
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            $table->string('session_token', 100)->nullable();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->timestamp('recovery_contacted_at')->nullable();
            $table->text('recovery_note')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'session_token'], 'carts_tenant_session_unique');
            $table->unique(['tenant_id', 'customer_id'], 'carts_tenant_customer_unique');
            $table->index(['tenant_id', 'updated_at'], 'carts_tenant_updated_idx');
        });

        // 2. Cart Items Table
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->unique(['cart_id', 'product_variant_id'], 'cart_items_cart_variant_unique');
            $table->index(['cart_id', 'product_id'], 'cart_items_cart_product_idx');
        });

        // Add database-level check constraints
        $driver = DB::connection()->getDriverName();
        if (in_array($driver, ['mysql', 'pgsql'], true)) {
            DB::statement('ALTER TABLE carts ADD CONSTRAINT carts_identity_check CHECK (customer_id IS NOT NULL OR session_token IS NOT NULL)');
            DB::statement('ALTER TABLE cart_items ADD CONSTRAINT cart_items_quantity_check CHECK (quantity >= 1)');
        } elseif ($driver === 'sqlite') {
            DB::statement("CREATE TRIGGER carts_identity_insert BEFORE INSERT ON carts WHEN NEW.customer_id IS NULL AND NEW.session_token IS NULL BEGIN SELECT RAISE(ABORT, 'customer_id or session_token required'); END;");
            DB::statement("CREATE TRIGGER carts_identity_update BEFORE UPDATE ON carts WHEN NEW.customer_id IS NULL AND NEW.session_token IS NULL BEGIN SELECT RAISE(ABORT, 'customer_id or session_token required'); END;");
            DB::statement("CREATE TRIGGER cart_items_qty_insert BEFORE INSERT ON cart_items WHEN NEW.quantity < 1 BEGIN SELECT RAISE(ABORT, 'quantity must be >= 1'); END;");
            DB::statement("CREATE TRIGGER cart_items_qty_update BEFORE UPDATE ON cart_items WHEN NEW.quantity < 1 BEGIN SELECT RAISE(ABORT, 'quantity must be >= 1'); END;");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
