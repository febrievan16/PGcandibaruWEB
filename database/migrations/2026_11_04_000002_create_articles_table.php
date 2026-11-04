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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 180)->unique();
            $table->string('title', 200);
            $table->string('category', 80)->default('Arsip & Sejarah');
            $table->string('author_name', 120)->default('Tim Kurator Sejarah PG Candi Baru');
            $table->text('excerpt');
            $table->longText('content');
            $table->string('cover_image', 255)->nullable();
            $table->unsignedSmallInteger('reading_time_minutes')->default(4);
            $table->boolean('is_published')->default(true)->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};

