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
        Schema::create('view_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->nullable()->comment('客户ID');
            $table->string('client_ip')->comment('客户IP');
            $table->string('language')->comment('客户语言');
            $table->string('referrer')->nullable()->comment('来源地址');
            $table->string('page_url')->comment('访问URL');
            $table->string('method')->comment('请求方法');
            $table->string('status_code')->comment('返回状态码');
            $table->text('user_agent')->comment('浏览器信息');
            $table->string('country')->comment('所属国家');
            $table->string('city')->comment('所属城市');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //Schema::dropIfExists('view_logs');
    }
};
