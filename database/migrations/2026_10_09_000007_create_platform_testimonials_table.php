<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_testimonials', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('text');
            $table->string('merchant_name');
            $table->string('merchant_title')->nullable();
            $table->text('merchant_image')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=active, 0=inactive');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_testimonials');
    }
};
