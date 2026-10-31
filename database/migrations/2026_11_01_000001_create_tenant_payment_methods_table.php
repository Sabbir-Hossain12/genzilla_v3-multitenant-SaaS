<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenant_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 50); // cod, bkash_manual, nagad_manual, rocket_manual, bank_transfer, sslcommerz, bkash_api, stripe
            $table->string('type', 30); // cash, manual, automated
            $table->string('title', 150); // e.g. "Cash on Delivery", "Pay via bKash (Send Money)"
            $table->text('instructions')->nullable(); // manual payment steps or terms
            $table->text('credentials')->nullable(); // encrypted:array in Eloquent model
            $table->boolean('is_sandbox')->default(false); // true for sandbox/test mode
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'provider']);
            $table->index(['tenant_id', 'is_active', 'sort_order']);
            $table->index(['tenant_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_payment_methods');
    }
};
