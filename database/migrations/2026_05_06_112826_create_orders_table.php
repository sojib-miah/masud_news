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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->enum('delivery_type', ['inside_dhaka', 'outside_dhaka', 'shop']);
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();

            $table->string('payment_method');

            $table->integer('subtotal');
            $table->integer('delivery_charge');
            $table->integer('total');
            $table->enum('status', ['processing', 'canceled', 'delivered'])->default('processing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
