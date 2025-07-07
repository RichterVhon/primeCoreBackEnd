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
        Schema::create('office_turnover_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_space_listing_id')
                ->constrained('office_space_listings')
                ->onDelete('cascade');
            $table->string('handover')->nullable(); //make enum later on in the project
            $table->string('ceiling')->nullable();
            $table->string('wall')->nullable();
            $table->string('turnover_remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_turnover_conditions');
    }
};
