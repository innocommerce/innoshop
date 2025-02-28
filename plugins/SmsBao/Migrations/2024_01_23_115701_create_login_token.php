<?php

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
        if (! Schema::hasTable('sms_bao_login_token')) {
            Schema::create('sms_bao_login_token', function (Blueprint $table) {
                $table->comment('登录授权信息');
                $table->id()->comment('ID');
                $table->integer('user_id')->comment('用户id');
                $table->string('index_id')->comment('编号');
                $table->string('token')->comment('长期授权token');
                $table->dateTime('expire_time')->comment('过期时间');
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
