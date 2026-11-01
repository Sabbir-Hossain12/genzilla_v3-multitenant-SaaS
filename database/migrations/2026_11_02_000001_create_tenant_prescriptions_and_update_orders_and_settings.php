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
        // 1. Direct Prescriptions Queue (Assisted Ordering Inbox)
        Schema::create('tenant_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('prescription_number', 50);
            $table->string('customer_name', 150);
            $table->string('customer_phone', 50);
            $table->string('customer_email', 150)->nullable();
            $table->text('delivery_address');
            $table->string('delivery_city', 100)->nullable();
            $table->string('delivery_area', 100)->nullable();
            $table->text('patient_notes')->nullable();
            $table->string('prescription_image', 255);
            $table->enum('status', ['pending_review', 'converted_to_order', 'rejected'])->default('pending_review');
            $table->text('rejection_reason')->nullable();
            $table->text('pharmacist_notes')->nullable();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'prescription_number']);
            $table->index(['tenant_id', 'status', 'created_at']);
            $table->index(['tenant_id', 'customer_phone']);
        });

        // 2. Enhance Orders with Prescription Attachments and Review Info
        Schema::table('orders', function (Blueprint $table) {
            $table->string('prescription_image', 255)->nullable()->after('prescription_status');
            $table->text('pharmacist_rx_notes')->nullable()->after('prescription_image');
            $table->foreignId('rx_reviewed_by_user_id')->nullable()->after('pharmacist_rx_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('rx_reviewed_at')->nullable()->after('rx_reviewed_by_user_id');
            $table->boolean('is_direct_prescription')->default(false)->after('rx_reviewed_at');
        });

        // 3. Enhance Order Products with Dosage Instructions
        Schema::table('order_products', function (Blueprint $table) {
            $table->string('dosage_instructions', 255)->nullable()->after('requires_prescription');
        });

        // 4. Enhance Tenants with Rx Compliance Settings
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('rx_compliance_mode', 30)->default('flexible')->after('theme_settings'); // strict, flexible
            $table->boolean('enable_direct_prescriptions')->default(true)->after('rx_compliance_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_prescriptions');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['rx_reviewed_by_user_id']);
            $table->dropColumn([
                'prescription_image',
                'pharmacist_rx_notes',
                'rx_reviewed_by_user_id',
                'rx_reviewed_at',
                'is_direct_prescription',
            ]);
        });

        Schema::table('order_products', function (Blueprint $table) {
            $table->dropColumn('dosage_instructions');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'rx_compliance_mode',
                'enable_direct_prescriptions',
            ]);
        });
    }
};
