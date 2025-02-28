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
        if (! Schema::hasTable('sitemap_url')) {
            Schema::create('sitemap_url', function (Blueprint $table) {
                $table->comment('站点地图url');
                $table->id()->comment('ID');
                $table->string('type')->comment('类型');
                $table->integer('type_id')->comment('对应类型的数据id');
                $table->string('type_url')->nullable(false)->comment('对应类型的url');
                $table->double('priority')->default(0.5)->comment('权重');
                $table->integer('status')->default(0)->comment('状态：0.不生成，1.生成');
                $table->string('name')->nullable(true)->default(null)->comment('名称');

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
