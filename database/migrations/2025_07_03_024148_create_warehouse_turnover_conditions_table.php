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
        Schema::create('warehouse_turnover_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_listing_id')
                ->constrained()
                ->onDelete('cascade');
            $table->string('flooring_turnover')->nullable();
            $table->string('ceiling_turnover')->nullable();
            $table->string('wall_turnover')->nullable();
            $table->text('turnover_remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_turnover_conditions');
    }
};
