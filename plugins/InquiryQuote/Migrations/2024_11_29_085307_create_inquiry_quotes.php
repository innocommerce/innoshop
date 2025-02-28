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
        if (! Schema::hasTable('inquiry_quotes')) {
            Schema::create('inquiry_quotes', function (Blueprint $table) {
                $table->id();
                $table->integer('parent_id')->default(0)->comment('Parent ID');
                $table->integer('admin_id')->default(0)->comment('Admin ID or Salesman ID');
                $table->integer('seller_id')->default(0)->comment('Seller ID');
                $table->integer('customer_id')->comment('Customer ID');
                $table->integer('order_id')->nullable()->comment('Order ID');
                $table->string('number')->unique()->comment('Quote Number');
                $table->string('based')->nullable()->comment('salesman|seller');
                $table->decimal('total', 12)->comment('Total');
                $table->integer('shipping_address_id')->nullable()->comment('Shipping Address ID');
                $table->string('shipping_method_code')->nullable()->comment('Shipping Method Code');
                $table->text('comment')->nullable()->comment('Quote Comment');
                $table->string('status')->comment('Quote Status');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('inquiry_quote_histories')) {
            Schema::create('inquiry_quote_histories', function (Blueprint $table) {
                $table->comment('Order History');
                $table->bigIncrements('id')->comment('ID');
                $table->integer('inquiry_quote_id')->index('iq_id')->comment('Quote ID');
                $table->string('status')->comment('Quote Status');
                $table->boolean('notify')->comment('Notify Or Not');
                $table->text('comment')->nullable()->comment('Comment');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('inquiry_quote_items')) {
            Schema::create('inquiry_quote_items', function (Blueprint $table) {
                $table->id();
                $table->integer('inquiry_quote_id')->comment('Inquiry Quote ID');
                $table->integer('customer_id')->comment('Customer ID');
                $table->integer('product_id')->comment('Product ID');
                $table->integer('seller_id')->nullable()->comment('Seller ID');
                $table->string('sku_code')->comment('SKU Code');
                $table->integer('quantity')->comment('Quantity');
                $table->decimal('origin_price')->comment('Original Price');
                $table->decimal('inquiry_price')->comment('Inquiry Price');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('inquiry_quote_fees')) {
            Schema::create('inquiry_quote_fees', function (Blueprint $table) {
                $table->id();
                $table->integer('inquiry_quote_id')->comment('Inquiry Quote ID');
                $table->integer('seller_id')->nullable()->comment('Seller ID');
                $table->string('code')->comment('Fee Code');
                $table->string('label')->comment('Fee Label');
                $table->decimal('origin_amount')->comment('Original Amount');
                $table->decimal('inquiry_amount')->comment('Original Amount');
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('customers', 'admin_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->integer('admin_id')->default(0)->after('id')->comment('Admin ID or Salesman ID');
            });
        }
        if (! Schema::hasColumn('orders', 'admin_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->integer('admin_id')->default(0)->after('id')->comment('Admin ID or Salesman ID');
            });
        }
        if (! Schema::hasColumn('order_returns', 'admin_id')) {
            Schema::table('order_returns', function (Blueprint $table) {
                $table->integer('admin_id')->default(0)->after('id')->comment('Admin ID or Salesman ID');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
