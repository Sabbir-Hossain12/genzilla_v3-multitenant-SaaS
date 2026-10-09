<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_category_id')->nullable()->constrained('platform_blog_categories')->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('excerpt');
            $table->longText('long_desc')->nullable()->comment('Full article body as HTML markup');
            $table->string('cover_class')->nullable()->comment('Tailwind gradient classes for the cover');
            $table->string('emoji')->nullable();
            $table->string('author_name');
            $table->string('author_role')->nullable();
            $table->string('author_initials')->nullable();
            $table->unsignedSmallInteger('read_time')->default(1)->comment('Reading time in minutes');
            $table->date('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->tinyInteger('status')->default(1)->comment('1=published, 0=draft');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_blog_posts');
    }
};
