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
        Schema::create('ter_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('category');
            $table->bigInteger('min_bruto');
            $table->bigInteger('max_bruto')->nullable();
            $table->decimal('percentage', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ter_rates');
    }
};
