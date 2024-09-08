<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 *
 * https://github.com/kitloong/laravel-migrations-generator
 * php artisan migrate:generate --squash
 * php artisan migrate:generate --tables="table1,table2"
 * php artisan migrate:generate --ignore="table3,table4,table5"
 */

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
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->comment('Address');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedInteger('customer_id')->index('customer_id')->comment('Customer ID');
            $table->string('guest_id')->default('')->comment('Guest ID, like session id');
            $table->string('name')->comment('Customer Name');
            $table->string('email')->nullable()->comment('Email');
            $table->string('phone')->default('')->comment('Telephone');
            $table->unsignedInteger('country_id')->index('country_id')->comment('Country ID');
            $table->unsignedInteger('state_id')->index('state_id')->comment('state ID');
            $table->string('state')->comment('state Name');
            $table->unsignedInteger('city_id')->nullable()->index('city_id')->comment('City ID');
            $table->string('city')->comment('City Name');
            $table->string('zipcode')->comment('Zip Code');
            $table->string('address_1')->comment('Address 1');
            $table->string('address_2')->comment('Address 2');
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->comment('Admin User');
            $table->bigIncrements('id')->comment('ID');
            $table->string('name')->comment('Name');
            $table->string('email', 64)->unique()->comment('Email');
            $table->string('password')->comment('Password');
            $table->string('locale')->default('')->comment('Locale Code');
            $table->boolean('active')->default(true)->comment('Active');
            $table->timestamps();
        });

        Schema::create('article_products', function (Blueprint $table) {
            $table->comment('Article Related Products');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('article_id')->index('ap_article_id')->comment('Article ID');
            $table->integer('product_id')->index('ap_product_id')->comment('Product ID');
            $table->timestamps();
        });

        Schema::create('article_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('article_id')->comment('Article ID');
            $table->integer('tag_id')->comment('Tag ID');
            $table->timestamps();
        });

        Schema::create('article_translations', function (Blueprint $table) {
            $table->comment('Article Translations');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('article_id')->index('at_article_id')->comment('Article ID');
            $table->string('locale')->comment('Locale Code');
            $table->string('title')->comment('Title');
            $table->string('summary')->nullable()->comment('Summary');
            $table->string('image')->nullable()->comment('Article Image');
            $table->text('content')->nullable()->comment('Content');
            $table->string('meta_title')->nullable()->comment('Meta Title');
            $table->text('meta_description')->nullable()->comment('Meta description');
            $table->string('meta_keywords')->nullable()->comment('Meta keywords');
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->comment('Article');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('catalog_id')->nullable()->default(0)->index('a_catalog_id')->comment('Catalog ID');
            $table->string('slug', 128)->nullable()->unique()->comment('URL Slug');
            $table->integer('position')->default(0)->comment('Sort order');
            $table->integer('viewed')->default(0)->comment('Viewed');
            $table->string('author')->nullable()->comment('Author');
            $table->boolean('active')->default(true)->comment('Active');
            $table->timestamps();
        });

        Schema::create('attribute_group_translations', function (Blueprint $table) {
            $table->comment('Attribute Group Translations');
            $table->bigIncrements('id');
            $table->unsignedInteger('attribute_group_id')->comment('Attribute Group ID');
            $table->string('locale')->default('')->comment('Locale Code');
            $table->string('name')->default('')->comment('Name');
            $table->timestamps();

            $table->index(['attribute_group_id', 'locale'], 'attribute_group_id_locale');
        });

        Schema::create('attribute_groups', function (Blueprint $table) {
            $table->comment('Attribute Group');
            $table->bigIncrements('id');
            $table->integer('position')->default(0)->comment('Sort order');
            $table->timestamps();
        });

        Schema::create('attribute_translations', function (Blueprint $table) {
            $table->comment('Attribute Translations');
            $table->bigIncrements('id');
            $table->unsignedInteger('attribute_id')->comment('Attribute ID');
            $table->string('locale')->default('')->comment('Locale Code');
            $table->string('name')->default('')->comment('Name');
            $table->timestamps();

            $table->index(['attribute_id', 'locale'], 'attribute_id_locale');
        });

        Schema::create('attribute_value_translations', function (Blueprint $table) {
            $table->comment('Attribute Value Translations');
            $table->bigIncrements('id');
            $table->unsignedInteger('attribute_value_id')->index('attribute_value_id')->comment('Attribute Value ID');
            $table->string('locale')->default('')->comment('Locale Code');
            $table->string('name')->default('')->comment('Name');
            $table->timestamps();

            $table->index(['attribute_value_id', 'locale'], 'attribute_value_id_locale');
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->comment('Attribute Value');
            $table->bigIncrements('id');
            $table->unsignedInteger('attribute_id')->index('attribute_id')->comment('Attribute ID');
            $table->timestamps();
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->comment('Attribute');
            $table->bigIncrements('id');
            $table->unsignedInteger('category_id')->comment('Category ID');
            $table->unsignedInteger('attribute_group_id')->index('attribute_group_id')->comment('Attribute Group ID');
            $table->integer('position')->default(0)->comment('Sort order');
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->comment('Brand');
            $table->bigIncrements('id')->comment('ID');
            $table->string('name')->comment('Name');
            $table->string('slug', 128)->nullable()->unique()->comment('URL Slug');
            $table->char('first')->comment('First Letter');
            $table->string('logo')->comment('Logo');
            $table->integer('position')->default(0)->comment('Sort order');
            $table->boolean('active')->default(true)->comment('Active');
            $table->timestamps();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->comment('Cart Product Item');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('customer_id')->index('ci_customer_id')->comment('Customer ID');
            $table->integer('product_id')->index('ci_product_id')->comment('Product ID');
            $table->string('sku_code')->index('ci_sku_code')->comment('Product SKU Code');
            $table->string('guest_id')->default('')->comment('Guest ID, like session id');
            $table->boolean('selected')->comment('Selected');
            $table->unsignedInteger('quantity')->comment('Quantity');
            $table->timestamps();
        });

        Schema::create('catalog_translations', function (Blueprint $table) {
            $table->comment('Article Category Translation');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('catalog_id')->index('ct_catalog_id')->comment('Category ID');
            $table->string('locale')->comment('Locale Code');
            $table->string('title')->comment('Title');
            $table->text('summary')->nullable()->comment('Category Summary');
            $table->string('meta_title')->nullable()->comment('Meta Title');
            $table->text('meta_description')->nullable()->comment('Meta Translation');
            $table->string('meta_keywords')->nullable()->comment('Meta Keywords');
            $table->timestamps();
        });

        Schema::create('catalogs', function (Blueprint $table) {
            $table->comment('Article Category');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('parent_id')->default(0)->index('c_parent_id')->comment('Parent ID');
            $table->string('slug', 128)->nullable()->unique()->comment('URL Slug');
            $table->integer('position')->default(0)->comment('Sort order');
            $table->boolean('active')->default(true)->comment('Active');
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->comment('ProductCategory');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('parent_id')->default(0)->index('parent_id')->comment('Parent Category ID');
            $table->string('slug', 128)->nullable()->unique()->comment('URL Slug');
            $table->integer('position')->default(0)->comment('Sort order');
            $table->boolean('active')->default(true)->comment('Active');
            $table->timestamps();
        });

        Schema::create('category_paths', function (Blueprint $table) {
            $table->comment('Product Category Path');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('category_id')->index('cp_category_id')->comment('Category ID');
            $table->unsignedBigInteger('path_id')->index('cp_path_id')->comment('Category Path ID');
            $table->integer('level')->comment('Level');
            $table->timestamps();
        });

        Schema::create('category_translations', function (Blueprint $table) {
            $table->comment('Product Translation');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('category_id')->index('category_id')->comment('Category ID');
            $table->string('locale')->comment('Locale Code');
            $table->string('name')->comment('Name');
            $table->text('content')->comment('Content');
            $table->string('meta_title')->nullable()->comment('Meta Title');
            $table->text('meta_description')->nullable()->comment('meta  Translation');
            $table->string('meta_keywords')->nullable()->comment('Meta Keywords');
            $table->timestamps();
        });

        Schema::create('checkout', function (Blueprint $table) {
            $table->comment('Checkout');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('customer_id')->index('cart_customer_id')->comment('Customer ID');
            $table->string('guest_id')->default('')->comment('Guest ID, like session id');
            $table->integer('shipping_address_id')->index('c_sa_id')->comment('Shipping Address ID');
            $table->string('shipping_method_code')->comment('Shipping Method Code');
            $table->integer('billing_address_id')->index('c_ba_id')->comment('Billing Address ID');
            $table->string('billing_method_code')->comment('Billing Method Code');
            $table->json('reference')->nullable()->comment('Order Extra');
            $table->timestamps();
        });

        Schema::create('countries', function (Blueprint $table) {
            $table->comment('Country');
            $table->bigIncrements('id')->comment('ID');
            $table->string('name', 64)->comment('Name');
            $table->string('code', 16)->comment('Code');
            $table->string('continent', 100)->comment('Continent');
            $table->integer('position')->default(0)->comment('Sort order');
            $table->boolean('active')->default(true)->comment('Active');
            $table->timestamps();
        });

        Schema::create('currencies', function (Blueprint $table) {
            $table->comment('Currency');
            $table->bigIncrements('id')->comment('ID');
            $table->string('name', 64)->comment('Name');
            $table->string('code', 16)->comment('Code');
            $table->string('symbol_left', 16)->comment('Left Symbol');
            $table->string('symbol_right', 16)->comment('Right Symbol');
            $table->char('decimal_place', 1)->comment('Decimal place');
            $table->double('value')->comment('Currency Rate');
            $table->boolean('active')->default(true)->comment('Active');
            $table->timestamps();
        });

        Schema::create('customer_favorites', function (Blueprint $table) {
            $table->comment('Customer Favorite');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedInteger('customer_id')->index('cw_customer_id')->comment('Customer ID');
            $table->unsignedInteger('product_id')->index('cw_product_id')->comment('Product ID');
            $table->timestamps();
        });

        Schema::create('customer_group_translations', function (Blueprint $table) {
            $table->comment('Customer Group Translation');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedInteger('customer_group_id')->index('customer_group_id')->comment('Customer Group ID');
            $table->string('locale', 10)->comment('Locale Code');
            $table->string('name', 256)->comment('Name');
            $table->text('description')->comment(' Translation');
            $table->timestamps();
        });

        Schema::create('customer_groups', function (Blueprint $table) {
            $table->comment('Customer Group');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('level')->comment('Level');
            $table->decimal('mini_cost', 12, 4)->comment('Mini Cost Total');
            $table->decimal('discount_rate', 12, 4)->comment('Discount Rate');
            $table->timestamps();
        });

        Schema::create('customer_socials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('customer_id')->index('cs_customer_id');
            $table->string('provider');
            $table->string('user_id');
            $table->string('union_id');
            $table->text('access_token');
            $table->text('refresh_token');
            $table->text('reference');
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->comment('Customer');
            $table->bigIncrements('id')->comment('ID');
            $table->string('email', 64)->unique()->comment('Email');
            $table->string('password')->comment('Password');
            $table->string('name')->comment('Name');
            $table->string('avatar')->default('')->comment('Avatar');
            $table->unsignedInteger('customer_group_id')->default(0)->index('c_customer_group_id')->comment('Customer Group ID');
            $table->unsignedInteger('address_id')->default(0)->index('c_address_id')->comment('Default Address ID');
            $table->string('locale', 10)->default('')->comment('Locale Code');
            $table->boolean('active')->default(true)->comment('Active');
            $table->string('code', 40)->default('')->comment('Find Password Code');
            $table->string('from', 16)->default('')->comment('From');
            $table->softDeletes()->comment('Deleted Time');
            $table->timestamps();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid', 128)->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('locales', function (Blueprint $table) {
            $table->comment('Locales');
            $table->bigIncrements('id')->comment('ID');
            $table->string('name', 64)->comment('Name');
            $table->string('code', 16)->comment('Code');
            $table->string('image')->comment('Country Icon');
            $table->integer('position')->default(0)->comment('Sort order');
            $table->boolean('active')->default(true)->comment('Active');
            $table->timestamps();
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->comment('Model Permission');
            $table->unsignedBigInteger('permission_id')->comment('Permission ID');
            $table->string('model_type')->comment('Model Type');
            $table->unsignedBigInteger('model_id')->comment('Model ID');

            $table->index(['model_id', 'model_type']);
            $table->primary(['permission_id', 'model_id', 'model_type'], 'permission_model_type');
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->comment('Model Role');
            $table->unsignedBigInteger('role_id')->comment('Role ID');
            $table->string('model_type')->comment('Model Type');
            $table->unsignedBigInteger('model_id')->comment('Model ID');

            $table->index(['model_id', 'model_type']);
            $table->primary(['role_id', 'model_id', 'model_type'], 'role_model_type');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->char('id', 36)->primary()->comment('UUID');
            $table->string('type')->comment('Type');
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->text('data')->comment('Data');
            $table->timestamp('read_at')->nullable()->comment('Read At');
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id']);
        });

        Schema::create('order_fees', function (Blueprint $table) {
            $table->comment('Order Fees');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('order_id')->index('ot_order_id')->comment('Order ID');
            $table->string('code')->comment('Code');
            $table->decimal('value')->comment('Value');
            $table->string('title')->comment('Name');
            $table->json('reference')->nullable()->comment('Reference Information');
            $table->timestamps();
        });

        Schema::create('order_histories', function (Blueprint $table) {
            $table->comment('Order History');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('order_id')->index('oh_order_id')->comment('Order ID');
            $table->string('status')->comment('Order Status');
            $table->boolean('notify')->comment('Notify Or Not');
            $table->text('comment')->comment('Comment');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->comment('Order Item');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('order_id')->index('oi_order_id')->comment('Order ID');
            $table->integer('product_id')->index('oi_product_id')->comment('Product ID');
            $table->string('order_number')->comment('Order Number');
            $table->string('product_sku')->comment('Product SKU');
            $table->string('variant_label')->comment('Product SKU Labels');
            $table->string('name')->comment('Product Name');
            $table->string('image')->comment('Product Image');
            $table->integer('quantity')->comment('Quantity');
            $table->decimal('price', 16, 4)->comment('Unit Price');
            $table->timestamps();
            $table->softDeletes()->comment('Deleted At');
        });

        Schema::create('order_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('order_id')->index('op_order_id')->comment('Order ID');
            $table->string('charge_id')->comment('Charge ID');
            $table->decimal('amount')->comment('Paid Amount');
            $table->decimal('handling_fee')->comment('Handling Fee');
            $table->boolean('paid')->comment('Paid or not');
            $table->text('reference')->nullable()->comment('Proof of payment');
            $table->timestamps();
        });

        Schema::create('order_return_histories', function (Blueprint $table) {
            $table->comment('Order Return Histories');
            $table->bigIncrements('id')->comment('ID');
            $table->string('type');
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('order_return_items', function (Blueprint $table) {
            $table->comment('Order Return Products');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('order_return_id')->index('ri_order_return_id');
            $table->integer('order_product_id')->index('ri_op_id');
            $table->integer('product_id')->index('ri_product_id');
            $table->string('product_name');
            $table->string('product_sku');
            $table->integer('quantity');
            $table->tinyInteger('opened');
            $table->string('status');
            $table->text('comment');
            $table->timestamps();
        });

        Schema::create('order_return_payments', function (Blueprint $table) {
            $table->comment('Order Return Payments');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('order_return_id')->index('orp_order_return_id');
            $table->decimal('amount');
            $table->string('type');
            $table->string('status');
            $table->string('comment');
            $table->timestamps();
        });

        Schema::create('order_returns', function (Blueprint $table) {
            $table->comment('Order Return');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('customer_id')->index('or_customer_id');
            $table->integer('order_id')->index('or_order_id');
            $table->string('order_number');
            $table->string('number');
            $table->string('name');
            $table->string('email');
            $table->string('telephone');
            $table->timestamps();
        });

        Schema::create('order_shipments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('order_id')->index('os_order_id')->comment('Order ID');
            $table->string('express_code')->comment('Express Code');
            $table->string('express_company')->comment('Express Company Name');
            $table->string('express_number')->comment('Express Number');
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->comment('Order');
            $table->bigIncrements('id')->comment('ID');
            $table->string('number')->comment('Order Number');
            $table->integer('customer_id')->index('o_customer_id')->comment('Customer ID');
            $table->integer('customer_group_id')->index('o_cg_id')->comment('Customer Group ID');
            $table->integer('shipping_address_id')->index('o_sa_id')->comment('Shipping Address ID');
            $table->integer('billing_address_id')->index('o_pa_id')->comment('Billing Address  ID');
            $table->string('customer_name')->comment('Customer名称');
            $table->string('email')->comment('Customer Email');
            $table->integer('calling_code')->comment('Calling Code');
            $table->string('telephone')->comment('Telephone');
            $table->decimal('total', 16, 4)->comment('Total');
            $table->string('locale')->comment('Locale Code');
            $table->string('currency_code')->comment('Currency');
            $table->string('currency_value')->comment('Currency Rate');
            $table->string('ip')->comment('IP');
            $table->text('user_agent')->comment('User Agent');
            $table->string('status')->comment('Status');
            $table->string('shipping_method_code')->comment('Shipping Method Code');
            $table->string('shipping_method_name')->comment('Shipping Method Name');
            $table->string('shipping_customer_name')->comment('Shipping Address Customer Name');
            $table->string('shipping_calling_code')->comment('Shipping Address Calling Code');
            $table->string('shipping_telephone')->comment('Shipping Address Telephone');
            $table->string('shipping_country')->comment('Shipping Address Country');
            $table->unsignedInteger('shipping_country_id')->comment('Shipping Country ID');
            $table->unsignedInteger('shipping_state_id')->comment('Shipping State ID');
            $table->string('shipping_state')->comment('Shipping Address State');
            $table->string('shipping_city')->comment('Shipping Address City');
            $table->string('shipping_address_1')->comment('Shipping Address 1');
            $table->string('shipping_address_2')->comment('Shipping Address 2');
            $table->string('shipping_zipcode')->comment('Shipping Address Zipcode');
            $table->string('billing_method_code')->comment('Billing Method Code');
            $table->string('billing_method_name')->comment('Billing Method Name');
            $table->string('billing_customer_name')->comment('Billing Address Customer Name');
            $table->string('billing_calling_code')->comment('Billing Address Calling Code');
            $table->string('billing_telephone')->comment('Billing Address Telephone');
            $table->string('billing_country')->comment('Billing Address Country');
            $table->unsignedInteger('billing_country_id')->comment('Billing Country ID');
            $table->unsignedInteger('billing_state_id')->comment('Billing State ID');
            $table->string('billing_state')->comment('Billing Address State');
            $table->string('billing_city')->comment('Billing Address City');
            $table->string('billing_address_1')->comment('Billing Address 1');
            $table->string('billing_address_2')->comment('Billing Address 1');
            $table->string('billing_zipcode')->comment('Billing Address Zipcode');
            $table->timestamps();
            $table->softDeletes()->comment('Deleted At');
        });

        Schema::create('page_modules', function (Blueprint $table) {
            $table->comment('DIY Modules');
            $table->bigIncrements('id')->comment('ID');
            $table->string('name')->comment('Module Name');
            $table->string('code')->comment('Module Code');
            $table->json('data')->comment('Data');
            $table->timestamps();
        });

        Schema::create('page_translations', function (Blueprint $table) {
            $table->comment('Article Page');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('page_id')->index('pt_article_id')->comment('Article ID');
            $table->string('locale')->comment('Locale Code');
            $table->string('title')->comment('Title');
            $table->text('content')->nullable()->comment('Content');
            $table->text('template')->nullable()->comment('Content');
            $table->string('meta_title')->nullable()->comment('Meta Title');
            $table->text('meta_description')->nullable()->comment('Meta description');
            $table->string('meta_keywords')->nullable()->comment('Meta keywords');
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->comment('Page');
            $table->bigIncrements('id')->comment('ID');
            $table->string('slug', 128)->nullable()->unique()->comment('URL Slug');
            $table->integer('position')->default(0)->comment('Sort order');
            $table->integer('viewed')->default(0)->comment('Viewed');
            $table->boolean('active')->default(true)->comment('Active');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->comment('Permission');
            $table->bigIncrements('id')->comment('ID');
            $table->string('name', 32)->comment('Name');
            $table->string('guard_name', 32)->comment('Guard Name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create('plugins', function (Blueprint $table) {
            $table->comment('Plugin');
            $table->bigIncrements('id')->comment('ID');
            $table->string('type')->comment('Type: shipping, payment');
            $table->string('code')->comment('Code, Unique');
            $table->integer('priority')->comment('Plugin Priority');
            $table->timestamps();
        });

        Schema::create('product_attributes', function (Blueprint $table) {
            $table->comment('Product Attribute');
            $table->bigIncrements('id');
            $table->unsignedInteger('product_id')->index('pa_product_id')->comment('Product ID');
            $table->unsignedInteger('attribute_id')->index('pa_attribute_id')->comment('Attribute ID');
            $table->unsignedInteger('attribute_value_id')->index('pa_attribute_value_id')->comment('Attribute Value ID');
            $table->timestamps();

            $table->index(['product_id', 'attribute_id'], 'pa_product_attribute_id');
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->comment('Product Category');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('product_id')->index('pc_product_id')->comment('Product ID');
            $table->unsignedBigInteger('category_id')->index('pc_category_id')->comment('Category ID');
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->comment('Product Image');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('product_id')->index('pi_product_id')->comment('Product ID');
            $table->string('path')->comment('Image Path');
            $table->integer('position')->default(0)->comment('Sort Order');
            $table->timestamps();
        });

        Schema::create('product_relations', function (Blueprint $table) {
            $table->comment('Related Product');
            $table->bigIncrements('id');
            $table->unsignedInteger('product_id')->index('pr_product_id')->comment('Product ID');
            $table->unsignedInteger('relation_id')->index('pr_relation_id')->comment('Related Product ID');
            $table->timestamps();
        });

        Schema::create('product_skus', function (Blueprint $table) {
            $table->comment('Product SKU');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('product_id')->index('ps_product_id')->comment('Product ID');
            $table->unsignedInteger('product_image_id')->default(0)->index('ps_pi_id')->comment('Image ID');
            $table->json('variants')->nullable()->comment('Variants Data');
            $table->string('code', 128)->unique('sku_code')->comment('SKU Code');
            $table->string('model')->default('')->comment('Model');
            $table->double('price')->default(0)->comment('Price');
            $table->double('origin_price')->default(0)->comment('Origin Price');
            $table->integer('quantity')->default(0)->comment('Inventory');
            $table->boolean('is_default')->comment('Default Or Not');
            $table->integer('position')->default(0)->comment('Sort order');
            $table->timestamps();
        });

        Schema::create('product_translations', function (Blueprint $table) {
            $table->comment('Product Translations');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('product_id')->index('pt_product_id')->comment('Product ID');
            $table->string('locale')->comment('Locale Code');
            $table->string('name')->comment('Name');
            $table->text('summary')->nullable()->comment('Summary');
            $table->text('content')->comment('Content');
            $table->string('meta_title')->nullable()->comment('Meta Title');
            $table->text('meta_description')->nullable()->comment('Meta Description');
            $table->string('meta_keywords')->nullable()->comment('Meta Keywords');
            $table->timestamps();
        });

        Schema::create('product_videos', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->integer('product_id')->index('pv_product_id')->comment('Product ID');
            $table->string('type', 11)->comment('Type: path or embed');
            $table->text('content')->comment('Video Path or Embed HTML');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->comment('Product');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedInteger('brand_id')->index('brand_id')->comment('Brand ID');
            $table->unsignedInteger('product_image_id')->default(0)->index('p_pi_id')->comment('Image ID');
            $table->unsignedInteger('product_video_id')->default(0)->index('p_pv_id')->comment('Video ID');
            $table->unsignedInteger('product_sku_id')->default(0)->index('p_ps_id')->comment('SKU ID');
            $table->unsignedInteger('tax_class_id')->default(0)->index('p_tc_id')->comment('Tax Class ID');
            $table->string('slug', 128)->nullable()->unique()->comment('URL Slug');
            $table->json('variables')->nullable()->comment('Product variables for sku with variants');
            $table->boolean('is_virtual')->default(false)->comment('Is Virtual');
            $table->integer('position')->default(0)->comment('Sort order');
            $table->boolean('active')->default(true)->comment('Active');
            $table->decimal('weight')->default(0)->comment('Weight');
            $table->string('weight_class')->default('')->comment('Weight Class');
            $table->integer('sales')->default(0)->comment('Sales');
            $table->integer('viewed')->default(0)->comment('Viewed');
            $table->timestamp('published_at')->nullable()->comment('Published At');
            $table->softDeletes()->comment('Deleted At');
            $table->timestamps();
        });

        Schema::create('region_states', function (Blueprint $table) {
            $table->comment('Region State');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('region_id')->index('rs_region_id')->comment('Region ID');
            $table->integer('country_id')->index('rs_country_id')->comment('Country ID');
            $table->integer('state_id')->index('rs_state_id')->comment('State ID');
            $table->timestamps();
        });

        Schema::create('regions', function (Blueprint $table) {
            $table->comment('Region');
            $table->bigIncrements('id')->comment('ID');
            $table->string('name')->comment('Region Name');
            $table->string('description')->comment('Regin Description');
            $table->integer('position')->default(0)->comment('Sort order');
            $table->boolean('active')->default(true)->comment('Active');
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->comment('Review');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('customer_id')->nullable()->index('rv_customer_id');
            $table->integer('product_id')->nullable()->index('rv_product_id');
            $table->integer('order_item_id')->nullable()->index('rv_oi_id');
            $table->integer('rating');
            $table->string('title');
            $table->string('content');
            $table->integer('like');
            $table->integer('dislike');
            $table->boolean('active')->default(true)->comment('Active');
            $table->timestamps();
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->comment('Role Permission');
            $table->unsignedBigInteger('permission_id')->comment('Permission ID');
            $table->unsignedBigInteger('role_id')->index('rhp_role')->comment('Role ID');

            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->comment('Role');
            $table->bigIncrements('id')->comment('ID');
            $table->string('name', 32)->comment('Role Name');
            $table->string('guard_name', 32)->comment('Guard Name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->comment('Setting');
            $table->bigIncrements('id')->comment('ID');
            $table->string('space')->comment('Group, Like: system, stripe, paypal');
            $table->string('name')->comment('Field Name');
            $table->text('value')->comment('Field Value');
            $table->boolean('json')->default(false)->comment('JSON Or Not');
            $table->timestamps();
        });

        Schema::create('states', function (Blueprint $table) {
            $table->comment('State');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedInteger('country_id')->index('s_country_id')->comment('Country ID');
            $table->string('country_code')->comment('Country Code');
            $table->string('name', 64)->comment('Name');
            $table->string('code', 16)->comment('Code');
            $table->integer('position')->default(0)->comment('Sort order');
            $table->boolean('active')->default(true)->comment('Active');
            $table->timestamps();
        });

        Schema::create('tag_translations', function (Blueprint $table) {
            $table->comment('Article Tag Translation');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('tag_id')->nullable();
            $table->string('locale')->comment('Locale Code');
            $table->string('name')->comment('Name');
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->comment('Article Tag');
            $table->bigIncrements('id')->comment('ID');
            $table->string('slug', 128)->nullable()->unique()->comment('URL Slug');
            $table->integer('position')->default(0)->comment('Sort order');
            $table->boolean('active')->default(true)->comment('Active');
            $table->timestamps();
        });

        Schema::create('tax_classes', function (Blueprint $table) {
            $table->comment('Tax Class');
            $table->bigIncrements('id')->comment('ID');
            $table->string('name')->comment('Tax Class Name');
            $table->string('description')->comment('Tax Description');
            $table->timestamps();
        });

        Schema::create('tax_rates', function (Blueprint $table) {
            $table->comment('Tax Rate');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('region_id')->index('region_id')->comment('Region ID');
            $table->string('name')->comment('Tax Rate Name');
            $table->enum('type', ['fixed', 'percent'])->comment('Type, fixed and percent');
            $table->decimal('rate')->comment('Rate');
            $table->timestamps();
        });

        Schema::create('tax_rules', function (Blueprint $table) {
            $table->comment('Tax Rule');
            $table->bigIncrements('id')->comment('ID');
            $table->integer('tax_class_id')->index('tr_tax_class_id')->comment('Tax Class ID');
            $table->integer('tax_rate_id')->index('tr_tax_rate_id')->comment('Tax Rate ID');
            $table->enum('based', ['shipping', 'billing', 'store'])->comment('Address Type');
            $table->integer('priority')->comment('Priority');
            $table->timestamps();
        });

        Schema::create('verify_codes', function (Blueprint $table) {
            $table->comment('Verify Code');
            $table->bigIncrements('id')->comment('ID');
            $table->string('account', 256)->comment('Account');
            $table->string('code', 16)->comment('Code');
            $table->softDeletes()->comment('Deleted At');
            $table->timestamps();
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->foreign(['permission_id'], 'mhp_permission')->references(['id'])->on('permissions')->onUpdate('restrict')->onDelete('cascade');
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->foreign(['role_id'], 'mhr_role')->references(['id'])->on('roles')->onUpdate('restrict')->onDelete('cascade');
        });

        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->foreign(['permission_id'], 'rhp_permission')->references(['id'])->on('permissions')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['role_id'], 'rhp_role')->references(['id'])->on('roles')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->dropForeign('rhp_permission');
            $table->dropForeign('rhp_role');
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropForeign('mhr_role');
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropForeign('mhp_permission');
        });

        Schema::dropIfExists('verify_codes');

        Schema::dropIfExists('tax_rules');

        Schema::dropIfExists('tax_rates');

        Schema::dropIfExists('tax_classes');

        Schema::dropIfExists('tags');

        Schema::dropIfExists('tag_translations');

        Schema::dropIfExists('states');

        Schema::dropIfExists('settings');

        Schema::dropIfExists('sessions');

        Schema::dropIfExists('roles');

        Schema::dropIfExists('role_has_permissions');

        Schema::dropIfExists('reviews');

        Schema::dropIfExists('regions');

        Schema::dropIfExists('region_states');

        Schema::dropIfExists('products');

        Schema::dropIfExists('product_videos');

        Schema::dropIfExists('product_translations');

        Schema::dropIfExists('product_skus');

        Schema::dropIfExists('product_relations');

        Schema::dropIfExists('product_images');

        Schema::dropIfExists('product_categories');

        Schema::dropIfExists('product_attributes');

        Schema::dropIfExists('plugins');

        Schema::dropIfExists('permissions');

        Schema::dropIfExists('password_reset_tokens');

        Schema::dropIfExists('pages');

        Schema::dropIfExists('page_translations');

        Schema::dropIfExists('page_modules');

        Schema::dropIfExists('orders');

        Schema::dropIfExists('order_shipments');

        Schema::dropIfExists('order_returns');

        Schema::dropIfExists('order_return_payments');

        Schema::dropIfExists('order_return_items');

        Schema::dropIfExists('order_return_histories');

        Schema::dropIfExists('order_payments');

        Schema::dropIfExists('order_items');

        Schema::dropIfExists('order_histories');

        Schema::dropIfExists('order_fees');

        Schema::dropIfExists('notifications');

        Schema::dropIfExists('model_has_roles');

        Schema::dropIfExists('model_has_permissions');

        Schema::dropIfExists('locales');

        Schema::dropIfExists('jobs');

        Schema::dropIfExists('job_batches');

        Schema::dropIfExists('failed_jobs');

        Schema::dropIfExists('customers');

        Schema::dropIfExists('customer_socials');

        Schema::dropIfExists('customer_groups');

        Schema::dropIfExists('customer_group_translations');

        Schema::dropIfExists('customer_favorites');

        Schema::dropIfExists('currencies');

        Schema::dropIfExists('countries');

        Schema::dropIfExists('checkout');

        Schema::dropIfExists('category_translations');

        Schema::dropIfExists('category_paths');

        Schema::dropIfExists('categories');

        Schema::dropIfExists('catalogs');

        Schema::dropIfExists('catalog_translations');

        Schema::dropIfExists('cart_items');

        Schema::dropIfExists('cache_locks');

        Schema::dropIfExists('cache');

        Schema::dropIfExists('brands');

        Schema::dropIfExists('attributes');

        Schema::dropIfExists('attribute_values');

        Schema::dropIfExists('attribute_value_translations');

        Schema::dropIfExists('attribute_translations');

        Schema::dropIfExists('attribute_groups');

        Schema::dropIfExists('attribute_group_translations');

        Schema::dropIfExists('articles');

        Schema::dropIfExists('article_translations');

        Schema::dropIfExists('article_tags');

        Schema::dropIfExists('article_products');

        Schema::dropIfExists('admins');

        Schema::dropIfExists('addresses');
    }
};
