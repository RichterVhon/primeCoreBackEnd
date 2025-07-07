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
        Schema::create('office_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_space_listing_id')
                ->constrained('office_space_listings')
                ->onDelete('cascade');
            $table->string('density_ratio')->nullable(); 
            $table->float('floor_to_ceiling_height')->nullable();
            $table->float('floor_to_floor')->nullable();
            $table->string('accreditation')->nullable(); 
            $table->string('certification')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_specs');
    }
};
