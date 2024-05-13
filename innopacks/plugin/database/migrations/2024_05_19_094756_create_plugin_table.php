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
        if (! Schema::hasTable('plugins')) {
            Schema::create('plugins', function (Blueprint $table) {
                $table->comment('Plugin');
                $table->bigIncrements('id')->comment('ID');
                $table->string('type')->comment('Type: shipping, payment');
                $table->string('code')->comment('Code, Unique');
                $table->integer('priority')->comment('Plugin Priority');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->comment('Setting');
                $table->bigIncrements('id')->comment('ID');
                $table->string('space')->comment('Group, Like: system, stripe, paypal');
                $table->string('name')->comment('Field Name');
                $table->text('value')->comment('Field Value');
                $table->boolean('json')->default(false)->comment('JSON Or Not');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};
