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
        // 1. Customers Table
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('email', 150);
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('status', 30)->default('active'); // active, suspended, inactive
            $table->timestamps();

            $table->unique(['tenant_id', 'email']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'phone']);
        });

        // 2. Customer Addresses Table
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('title', 50)->default('Home'); // Home, Office, Billing, Shipping
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address_1', 255);
            $table->string('address_2', 255)->nullable();
            $table->string('state_district', 100)->nullable();
            $table->string('zip', 30)->nullable();
            $table->string('country', 100)->default('Bangladesh');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'customer_id']);
            $table->index(['customer_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
    }
};
