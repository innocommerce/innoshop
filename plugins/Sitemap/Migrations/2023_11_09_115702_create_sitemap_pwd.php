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
        if (! Schema::hasTable('sitemap_pwd')) {
            Schema::create('sitemap_pwd', function (Blueprint $table) {
                $table->comment('站点地图访问密码');
                $table->id()->comment('ID');
                $table->string('pwd')->comment('密码');
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
