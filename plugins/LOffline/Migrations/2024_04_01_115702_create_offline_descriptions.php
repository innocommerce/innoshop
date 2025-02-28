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
        if (! Schema::hasTable('offline_payment_config_descriptions')) {
            Schema::create('offline_payment_config_descriptions', function (Blueprint $table) {
                $table->comment('离线支付配置多语言');
                $table->id()->comment('ID');
                $table->text('content')->comment('内容');
                $table->string('locale')->comment('语言');
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
