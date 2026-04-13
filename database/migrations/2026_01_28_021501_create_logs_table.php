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
            $table->string('serial_no')->nullable(); // SL_NO
            $table->string('tag_no')->index();
            $table->string('log_no')->nullable();
            $table->string('species');
            $table->string('origin')->nullable();
            $table->decimal('length', 8, 3)->nullable();
            $table->decimal('girth_butt', 8, 2)->nullable(); // GB
            $table->decimal('girth_top', 8, 2)->nullable(); // PB
            $table->decimal('diameter', 8, 2)->nullable();
            $table->decimal('vol_cbm', 10, 6)->nullable();
            $table->decimal('l_ref', 10, 6)->nullable(); // Loss Reference
            $table->decimal('d_ref', 10, 6)->nullable(); // Decomposed Reference
            $table->string('buyer_name')->nullable();
            $table->text('remarks')->nullable();
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
