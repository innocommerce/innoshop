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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id')->nullable();
            $table->string('code')->unique();
            $table->enum('type', ['percentage', 'fixed']);
            $table->decimal('value', 8, 2);
            $table->dateTime('start_at')->default(now());
            $table->dateTime('end_at')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('is_used')->default(false);
            $table->unsignedInteger('times_used')->default(0);
            $table->integer('max_uses')->nullable(); // 优惠券的最大使用次数
            $table->integer('max_uses_per_user')->nullable(); // 每个用户的最大使用次数
            $table->integer('use_interval')->nullable(); // 使用间隔，小时为单位
            $table->unsignedInteger('daily_limit')->nullable();  // 每天的使用上限
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
