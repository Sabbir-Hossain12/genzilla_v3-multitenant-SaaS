<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'image')) {
                $table->string('image')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('categories', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('status');
            }
            if (!Schema::hasColumn('categories', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('categories', 'h1_heading')) {
                $table->string('h1_heading')->nullable()->after('meta_description');
            }
            if (!Schema::hasColumn('categories', 'canonical_url')) {
                $table->string('canonical_url')->nullable()->after('h1_heading');
            }
        });

        Schema::table('brands', function (Blueprint $table) {
            if (!Schema::hasColumn('brands', 'image')) {
                $table->string('image')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('brands', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('status');
            }
            if (!Schema::hasColumn('brands', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('brands', 'h1_heading')) {
                $table->string('h1_heading')->nullable()->after('meta_description');
            }
            if (!Schema::hasColumn('brands', 'canonical_url')) {
                $table->string('canonical_url')->nullable()->after('h1_heading');
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $drop = [];
            foreach (['image', 'meta_title', 'meta_description', 'h1_heading', 'canonical_url'] as $col) {
                if (Schema::hasColumn('categories', $col)) {
                    $drop[] = $col;
                }
            }
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });

        Schema::table('brands', function (Blueprint $table) {
            $drop = [];
            foreach (['image', 'meta_title', 'meta_description', 'h1_heading', 'canonical_url'] as $col) {
                if (Schema::hasColumn('brands', $col)) {
                    $drop[] = $col;
                }
            }
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
