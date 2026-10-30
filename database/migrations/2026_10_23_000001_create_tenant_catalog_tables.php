<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('image')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('status')->default(true);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('h1_heading')->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'parent_id']);
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('image')->nullable();
            $table->boolean('status')->default(true);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('h1_heading')->nullable();
            $table->string('canonical_url')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('attributes_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->string('value');
            $table->string('slug');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'attribute_id', 'slug']);
            $table->index(['tenant_id', 'attribute_id']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->enum('kind', ['general', 'medicine'])->default('general');
            $table->string('name');
            $table->string('slug');
            $table->longText('description')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('generic_name')->nullable();
            $table->boolean('requires_prescription')->default(false);
            $table->json('specs')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'status', 'created_at']);
            $table->index(['tenant_id', 'category_id']);
            $table->index(['tenant_id', 'generic_name']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku');
            $table->string('barcode')->nullable();
            $table->string('title');
            $table->decimal('price', 12, 2);
            $table->decimal('compare_at_price', 12, 2)->nullable();
            $table->decimal('mrp', 12, 2)->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->unsignedInteger('weight_grams')->nullable();
            $table->boolean('track_inventory')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->char('options_hash', 32);
            $table->timestamps();
            $table->unique(['tenant_id', 'sku']);
            $table->unique(['product_id', 'options_hash']);
            $table->index(['tenant_id', 'barcode']);
        });

        Schema::create('product_variant_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained('attributes_values')->cascadeOnDelete();
            $table->unique(['variant_id', 'attribute_id'], 'pvav_variant_attribute_unique');
            $table->unique(['variant_id', 'attribute_value_id'], 'pvav_variant_value_unique');
        });

        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->string('batch_no')->default('DEFAULT');
            $table->date('expiry_date')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('reserved')->default(0);
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->timestamp('received_at')->nullable();
            $table->enum('status', ['active', 'quarantined', 'recalled'])->default('active');
            $table->timestamps();
            $table->unique(['variant_id', 'batch_no']);
            $table->index(['tenant_id', 'variant_id', 'expiry_date']);
        });
        $driver = DB::connection()->getDriverName();
        if (in_array($driver, ['mysql', 'pgsql'], true)) {
            DB::statement('ALTER TABLE inventory_batches ADD CONSTRAINT inventory_batches_stock_check CHECK (quantity >= 0 AND reserved >= 0 AND reserved <= quantity)');
        } elseif ($driver === 'sqlite') {
            DB::statement("CREATE TRIGGER inventory_batches_stock_insert BEFORE INSERT ON inventory_batches WHEN NEW.quantity < 0 OR NEW.reserved < 0 OR NEW.reserved > NEW.quantity BEGIN SELECT RAISE(ABORT, 'Invalid inventory batch quantities'); END");
            DB::statement("CREATE TRIGGER inventory_batches_stock_update BEFORE UPDATE ON inventory_batches WHEN NEW.quantity < 0 OR NEW.reserved < 0 OR NEW.reserved > NEW.quantity BEGIN SELECT RAISE(ABORT, 'Invalid inventory batch quantities'); END");
        }

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('inventory_batches')->nullOnDelete();
            $table->enum('type', ['receive', 'reserve', 'release', 'sale', 'return', 'adjust', 'expire_writeoff']);
            $table->integer('qty_delta')->default(0);
            $table->integer('reserved_delta')->default(0);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['tenant_id', 'variant_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('collection')->default('default');
            $table->string('path');
            $table->string('mime', 150);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('position')->default(0);
            $table->string('alt')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['model_type', 'model_id']);
            $table->index(['tenant_id', 'collection']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_batches');
        Schema::dropIfExists('product_variant_attribute_values');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('attributes_values');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};
