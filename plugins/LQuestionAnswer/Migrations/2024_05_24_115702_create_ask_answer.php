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
        if (! Schema::hasTable('product_ask_answer')) {
            Schema::create('product_ask_answer', function (Blueprint $table) {
                $table->comment('产品问答');
                $table->id()->comment('ID');
                $table->integer('product_id')->nullable(false)->comment('商品id');
                $table->integer('customer_id')->default(0)->comment('商城用户 id');
                $table->string('user_name')->nullable(true)->comment('用户名字');
                $table->string('reply_id')->nullable(true)->comment('回复id');
                $table->text('content')->nullable(true)->comment('问答内容');
                $table->integer('agree')->default(0)->comment('点赞数量');
                $table->integer('not_agree')->default(0)->comment('踩的数量');
                $table->integer('status')->default(1)->comment('状态：1.待审核，2.显示，3.隐藏不显示');
                $table->integer('parent_id')->default(0)->comment('父级，用于回复');
                $table->string('user_img')->nullable(true)->comment('头像');
                $table->string('session_id')->nullable(true)->comment('用户的session_id,用户评论后可以立即看到，但其他人看不到');
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
