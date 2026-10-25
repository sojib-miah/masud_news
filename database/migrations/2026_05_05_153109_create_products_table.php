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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->decimal('price', 8, 2);
            $table->decimal('discount_price', 8, 2);
            $table->integer('quantity');
            $table->text('description')->nullable();
            $table->decimal('croy', 8, 2);
            $table->decimal('dipo', 8, 2);
            $table->string('dillar')->nullable();
            $table->string('sub_dillar')->nullable();
            $table->integer('percentage')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
