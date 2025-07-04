<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

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
        Schema::create('customer_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->index();
            $table->decimal('amount', 12, 4)->default(0)->comment('Amount');
            $table->string('account_type', 20)->default('bank')->comment('Account Type: bank, alipay, wechat');
            $table->string('account_number', 100)->nullable()->comment('Account Number');
            $table->string('bank_name', 100)->nullable()->comment('Bank Name');
            $table->string('bank_account', 100)->nullable()->comment('Bank Account');
            $table->string('status', 20)->default('pending')->comment('Status: pending, approved, rejected, paid');
            $table->text('comment')->nullable()->comment('User Comment');
            $table->text('admin_comment')->nullable()->comment('Admin Comment');
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_withdrawals');
    }
};
