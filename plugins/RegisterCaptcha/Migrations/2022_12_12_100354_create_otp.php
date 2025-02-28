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

        if (! Schema::hasTable('register_email_otp')) {
            Schema::create('register_email_otp', function (Blueprint $table) {
                $table->id();
                $table->string('email');
                $table->string('code');
                $table->dateTime('expire_time');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_socials');
    }
};
