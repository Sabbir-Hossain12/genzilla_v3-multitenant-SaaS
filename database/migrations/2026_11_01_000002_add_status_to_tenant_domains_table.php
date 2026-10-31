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
        Schema::table('tenant_domains', function (Blueprint $table) {
            if (!Schema::hasColumn('tenant_domains', 'status')) {
                $table->string('status', 30)->default('active')->after('dns_verified')->comment('pending_dns, active, ssl_pending, failed');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_domains', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_domains', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
