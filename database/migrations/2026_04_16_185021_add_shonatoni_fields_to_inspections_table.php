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
        Schema::table('inspections', function (Blueprint $table) {
            $table->integer('actual_length_ft')->nullable()->after('actual_length');
            $table->decimal('actual_length_in', 4, 1)->nullable()->after('actual_length_ft');
            $table->decimal('actual_mid_girth', 6, 2)->nullable()->after('actual_length_in');
            $table->decimal('actual_vol_cft', 10, 6)->nullable()->after('actual_vol_cbm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropColumn(['actual_length_ft', 'actual_length_in', 'actual_mid_girth', 'actual_vol_cft']);
        });
    }
};
