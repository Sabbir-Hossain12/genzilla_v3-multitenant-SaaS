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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            // Ownership
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Core Store Details
            $table->string('name');
            $table->string('email')->nullable()->comment('Public store contact email');
            $table->string('logo_path')->nullable();

            // Domain & Routing
            $table->string('subdomain')->unique()->comment('e.g., mystore (routes to mystore.mydomain.com)');
            $table->string('custom_domain')->nullable()->unique()->comment('e.g., www.brand.com');

            // Financial & Payments
            $table->string('stripe_account_id')->nullable()->unique()->comment('For connected merchant payouts');
            $table->tinyInteger('stripeOnboarded')->default(0)->comment('0=no, 1=yes');
            $table->string('currency', 3)->default('USD');

            // Localization & Operations
            $table->string('timezone')->default('UTC');
            $table->string('status')->default('active')->comment('active, suspended, archived');

            $table->softDeletes(); // Highly recommended to prevent accidental store deletion
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
