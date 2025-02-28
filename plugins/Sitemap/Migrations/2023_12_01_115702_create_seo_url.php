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
        if (! Schema::hasTable('seo_url')) {
            Schema::create('seo_url', function (Blueprint $table) {
                $table->comment('seo别名');
                $table->id()->comment('ID');
                $table->string('type')->nullable(false)->comment('类型');
                $table->integer('type_id')->default(0)->comment('类型对应的数据id');
                $table->string('url_name')->nullable(null)->comment('别名');
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
