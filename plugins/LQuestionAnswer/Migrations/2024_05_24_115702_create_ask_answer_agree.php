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
        if (! Schema::hasTable('product_ask_answer_agree')) {
            Schema::create('product_ask_answer_agree', function (Blueprint $table) {
                $table->comment('点赞记录');
                $table->id()->comment('ID');
                $table->integer('product_id')->nullable(false)->comment('商品id');
                $table->integer('ask_answer_id')->nullable(false)->comment('问答id');
                $table->integer('customer_id')->nullable(false)->comment('商城用户 id');
                $table->integer('type')->default(1)->comment('1.赞，2.踩');
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
