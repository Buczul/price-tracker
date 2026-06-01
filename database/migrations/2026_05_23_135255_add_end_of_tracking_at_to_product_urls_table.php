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
        Schema::table('product_urls', function (Blueprint $table) {
            $table->timestamp('end_of_tracking_at')->nullable()->after('store_name');
        });
    }

    public function down(): void
    {
        Schema::table('product_urls', function (Blueprint $table) {
            $table->dropColumn('end_of_tracking_at');
        });
    }
};
