<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('image')->nullable();
            $table->rememberToken();
            $table->boolean('termes')->default(false);
            $table->enum('role', ['pathock', 'reporter', 'motamot'])->nullable();
            $table->string('total_views')->nullable();
            $table->string('total_income')->nullable();
            $table->string('current_views')->nullable();
            $table->string('current_income')->nullable();
            $table->string('ref_id')->nullable();
            $table->string('referred_by')->nullable();
            $table->decimal('earning_from_views', 10, 2)->default(0);
            $table->decimal('generation_commission', 10, 2)->default(0);
            $table->decimal('total_earning', 10, 2)->default(0);
            $table->decimal('total_balance', 10, 2)->default(0);
            $table->integer('published_news')->default(0);
            $table->decimal('reporter_income', 10, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->decimal('marketing_wallet', 10, 2)->default(0);
            $table->decimal('daily_return_wallet', 10, 2)->default(0);
            $table->decimal('gift_wallet', 10, 2)->default(0);
            $table->enum('status', ['pending', 'approved'])->default('pending');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
