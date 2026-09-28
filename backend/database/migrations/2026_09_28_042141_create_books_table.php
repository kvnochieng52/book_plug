<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('author');
            $table->text('description');
            $table->unsignedInteger('pages')->default(0);
            $table->string('language')->default('English');
            $table->date('published_at')->nullable();
            $table->string('isbn')->nullable();

            $table->boolean('has_digital')->default(true);
            $table->boolean('has_physical')->default(false);
            $table->decimal('digital_price', 8, 2)->default(0);
            $table->decimal('physical_price', 8, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('weight_grams')->default(0);

            $table->string('cover_path')->nullable();
            $table->string('pdf_path')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->json('tags')->nullable();

            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);

            $table->timestamps();

            $table->index(['is_published', 'is_featured']);
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
