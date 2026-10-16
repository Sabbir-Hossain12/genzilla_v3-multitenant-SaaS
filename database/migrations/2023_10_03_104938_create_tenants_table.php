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
            $table->string('store_name');
            $table->string('support_phone')->nullable();
            $table->string('support_email')->nullable()->comment('Public store contact email');
            $table->string('logo_path')->nullable();

            // Domain & Routing
            $table->string('subdomain')->unique()->comment('e.g., mystore (routes to mystore.mydomain.com)');
            $table->string('custom_domain')->nullable()->unique()->comment('e.g., www.brand.com');

            // Financial & Payments
            $table->string('stripe_account_id')->nullable()->unique()->comment('For connected merchant payouts');
            $table->tinyInteger('stripeOnboarded')->default(0)->comment('0=no, 1=yes');
            $table->string('currency', 3)->default('USD');

            $table->string('fb_pixel_id')->nullable();
            $table->string('google_analytics_id')->nullable();
            $table->string('gtm_id')->nullable();
            $table->text('custom_head_script')->nullable();

            $table->integer('current_subscription_id')->nullable();
            $table->enum('subscription_status',['trialing','active','past_due','expired','cancelled'])->default('trialing');
            $table->json('theme_settings')->nullable();


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
