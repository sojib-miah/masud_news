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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('language');
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('metatag')->nullable();
            $table->string('tag')->nullable();
            $table->longText('description');
            $table->string('image')->nullable();
            $table->string('imagenote')->nullable();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('featured')->default(0);
            $table->boolean('breaking')->default(0);
            $table->date('schedule')->nullable();
            $table->date('expire')->nullable();
            $table->boolean('status')->default(0);
            $table->integer('total_view')->default(0);
            $table->integer('current_view')->default(0);
            $table->string('audio');
            $table->string('video');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
