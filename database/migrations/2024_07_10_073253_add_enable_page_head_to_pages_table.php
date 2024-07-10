<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('enable_page_head')->default(true)->after('active')->comment('enable page head');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            //
            $table->dropColumn('enable_page_head');
        });
    }
};
