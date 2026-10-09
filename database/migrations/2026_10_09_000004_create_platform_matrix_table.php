<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_matrix', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('active_merchant')->default(0);
            $table->decimal('gmv_proccessed', 15, 2)->default(0.00);
            $table->unsignedInteger('countries_served')->default(0);
            $table->string('platform_uptime')->default('99.9%');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_matrix');
    }
};
