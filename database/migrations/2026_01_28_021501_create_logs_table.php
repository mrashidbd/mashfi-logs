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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->string('serial_no')->nullable();       // SN
            $table->string('log_no')->nullable();           // LOG.NO.
            $table->string('tag_no')->index();              // DF10-TAG.NO.
            $table->string('species');                      // SPECIES
            $table->string('origin')->nullable();           // ORIGIN
            $table->decimal('length', 8, 2)->nullable();    // LENGTH
            $table->decimal('girth_butt', 8, 2)->nullable(); // GB
            $table->decimal('girth_top', 8, 2)->nullable();  // PB
            $table->decimal('diameter', 8, 2)->nullable();   // DIA
            $table->decimal('l_ref', 8, 2)->nullable();      // L.REF
            $table->decimal('d_ref', 8, 2)->nullable();      // D.REF
            $table->decimal('calc_length', 8, 2)->nullable(); // CALC.LENGTH
            $table->decimal('vol_cbm', 10, 3)->nullable();   // VOLUME(CBM)
            $table->text('remarks')->nullable();              // REMARKS
            $table->string('buyer_name')->nullable();         // BUYER
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
