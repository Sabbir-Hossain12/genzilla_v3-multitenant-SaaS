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
        Schema::create('basic_infos', function (Blueprint $table) {
            $table->id();
            $table->string('platform_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_1')->nullable();
            $table->string('fb_link')->nullable();
            $table->string('x_link')->nullable();
            $table->string('p_link')->nullable();
            $table->string('youtube_link')->nullable();
            $table->string('insta_link')->nullable();
            $table->integer('inside_dhaka_charge')->nullable();
            $table->integer('outside_dhaka_charge')->nullable();

            $table->text('store_location')->nullable();
            $table->text('fb_pixel')->nullable();
            $table->text('google_analytics')->nullable();
            $table->text('chatbox_script')->nullable();
            $table->text('marquee_text')->nullable();

            $table->string('tagline')->nullable();
            $table->string('working_hours')->nullable();
            $table->string('copyright_text')->nullable();
            $table->string('app_download_link')->nullable();
            $table->string('app_download_img')->nullable();
            $table->string('payment_methods_img')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basic_infos');
    }
};
