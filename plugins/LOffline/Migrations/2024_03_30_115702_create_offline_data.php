<?php

/**
 * @author     村长+ <178277164@qq.com>
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('offline_payment_order')) {
            Schema::create('offline_payment_order', function (Blueprint $table) {
                $table->comment('离线支付订单数据');
                $table->id()->comment('ID');
                $table->integer('order_id')->comment('对应系统订单ID');
                $table->json('imgs')->nullable(false)->comment('凭证图片地址');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
