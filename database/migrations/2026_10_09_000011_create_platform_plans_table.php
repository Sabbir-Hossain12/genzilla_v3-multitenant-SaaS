<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('monthly_price', 10, 2)->default(0.00);
            $table->decimal('yearly_price', 10, 2)->default(0.00);
            $table->string('stripe_monthly_price_id')->nullable();
            $table->string('stripe_yearly_price_id')->nullable();
            $table->integer('max_products')->default(50);
            $table->integer('max_staff_accounts')->default(1);
            $table->integer('max_storage_mb')->default(100);
            $table->integer('max_digital_file_size_mb')->default(10);
            $table->boolean('allow_custom_domain')->default(false);
            $table->boolean('has_multi_currency')->default(false);
            $table->integer('max_currencies_supported')->default(1);
            $table->boolean('has_advanced_reporting')->default(false);
            $table->boolean('has_custom_rbac')->default(false);
            $table->boolean('has_audit_logs')->default(false);
            $table->tinyInteger('status')->default(1)->comment('1=active, 0=inactive');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_plans');
    }
};
