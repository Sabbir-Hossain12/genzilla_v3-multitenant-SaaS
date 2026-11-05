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
        Schema::table('tenant_invoices', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('platform_subscription_id')->constrained('platform_plans')->nullOnDelete();
            $table->string('plan_name')->nullable()->after('plan_id');
            $table->string('billing_interval')->nullable()->after('plan_name');
            $table->string('description')->nullable()->after('billing_interval');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_invoices', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['plan_id', 'plan_name', 'billing_interval', 'description']);
        });
    }
};
