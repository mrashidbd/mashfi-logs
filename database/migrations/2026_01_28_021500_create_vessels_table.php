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
        Schema::create('vessels', function (Blueprint $table) {
            $table->id();
            $table->string('vessel_code')->unique();
            $table->string('vessel_name')->nullable();
            $table->date('arrival_date')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->boolean('surveyor_access')->default(false);
            $table->boolean('buyer_access')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vessels');
    }
};
