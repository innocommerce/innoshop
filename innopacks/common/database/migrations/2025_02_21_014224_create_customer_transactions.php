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
        if (! Schema::hasColumn('customers', 'balance')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->decimal('balance')->default(0)->after('name')->comment('Customer Name');
            });
        }

        if (! Schema::hasTable('customer_transactions')) {
            Schema::create('customer_transactions', function (Blueprint $table) {
                $table->id();
                $table->integer('customer_id')->index()->comment('Customer ID');
                $table->decimal('amount')->comment('Amount');
                $table->string('type')->comment('Transaction Type');
                $table->text('comment')->nullable()->comment('Comment');
                $table->decimal('balance')->nullable()->comment('Current Balance');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_transactions');
    }
};
