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
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('platform_name')->default('MySaaS');
            $table->string('tagline')->nullable();
            $table->text('short_desc')->nullable();
            $table->text('black_logo')->nullable();
            $table->text('light_logo')->nullable();
            $table->text('favicon')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_1')->nullable();
            $table->text('company_address')->nullable();
            $table->string('working_hours')->nullable();
            $table->string('default_currency')->default('USD');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('meta_robots')->default('index, follow');
            $table->string('canonical_url')->nullable();
            $table->text('schema_markup')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->text('og_image')->nullable();
            $table->text('fb_pixel')->nullable();
            $table->text('google_analytics')->nullable();
            $table->text('chatbox_script')->nullable();
            $table->string('fb_link')->nullable();
            $table->string('x_link')->nullable();
            $table->string('youtube_link')->nullable();
            $table->string('insta_link')->nullable();
            $table->text('announcement_bar_text')->nullable();
            $table->string('copyright_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
