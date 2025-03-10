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
        if (! Schema::hasColumn('customers', 'referral_code')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('referral_code')->default('')->after('id')->unique()->comment('分销码');
                $table->integer('referrer_id')->default(0)->after('id')->comment('推荐人ID');
            });
        }

        if (! Schema::hasColumn('orders', 'referrer_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->integer('referrer_id')->default(0)->after('id')->comment('推荐人ID');
            });
        }

        if (! Schema::hasTable('referral_commissions')) {
            Schema::create('referral_commissions', function (Blueprint $table) {
                $table->id();
                $table->integer('order_id')->comment('订单ID');
                $table->integer('customer_id')->comment('订单客户ID');
                $table->integer('referrer_id')->comment('推荐人ID');
                $table->decimal('commission_amount')->comment('佣金');
                $table->string('status')->default('pending')->comment('状态: pending, paid, cancelled');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
