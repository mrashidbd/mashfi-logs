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
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('log_id')->constrained()->cascadeOnDelete();
            $table->foreignId('surveyor_id')->constrained('users');
            $table->enum('shift', ['A', 'B', 'C'])->nullable();
            $table->boolean('is_match')->default(true);

            // Actual findings (nullable if match, or redundant storage)
            // We store them if mismatch, or even if match for audit?
            // User requirement: "If not [matched], he needs to remeasure"
            // "if he approves... maybe we need to copy all data.. but if differs... enter actual findings"
            // Let's make them nullable. If is_match=true, these might be null or copy.
            // I'll make them nullable.
            $table->decimal('actual_length', 8, 3)->nullable();
            $table->decimal('actual_gb', 8, 2)->nullable();
            $table->decimal('actual_pb', 8, 2)->nullable();
            $table->decimal('actual_diameter', 8, 2)->nullable();
            $table->decimal('actual_vol_cbm', 10, 6)->nullable();
            $table->decimal('actual_l_ref', 10, 6)->nullable(); // Actual Loss Reference
            $table->decimal('actual_d_ref', 10, 6)->nullable(); // Actual Decomposed Reference
            $table->string('buyer_name')->nullable();

            $table->text('surveyor_remarks')->nullable();
            $table->json('images')->nullable(); // Paths to images

            $table->timestamp('verified_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
