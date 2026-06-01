<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Dodajemy kolumnę typu timestamp, domyślnie pustą (null)
            $table->timestamp('end_of_tracking_at')->nullable()->after('target_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('end_of_tracking_at');
        });
    }
};
