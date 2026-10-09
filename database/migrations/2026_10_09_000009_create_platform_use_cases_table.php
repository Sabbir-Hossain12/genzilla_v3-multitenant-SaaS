<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_use_cases', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('badge_label')->nullable();
            $table->string('badge_type')->nullable();
            $table->text('description')->nullable();
            $table->text('icon_url')->nullable();
            $table->string('bg_color')->nullable();
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status')->default(1)->comment('1=active, 0=inactive');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_use_cases');
    }
};
