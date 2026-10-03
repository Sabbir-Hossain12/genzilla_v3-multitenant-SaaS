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
        Schema::table('basic_infos', function (Blueprint $table) {
            $table->text('black_logo')->nullable()->after('id');
            $table->text('light_logo')->nullable()->after('black_logo');
            $table->text('short_desc')->nullable()->after('store_location');
            $table->string('currency_symbol')->nullable()->after('marquee_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('basic_infos', function (Blueprint $table) {
            $table->dropColumn([
                'black_logo',
                'light_logo',
                'short_desc',
                'currency_symbol',
            ]);
        });
    }
};
