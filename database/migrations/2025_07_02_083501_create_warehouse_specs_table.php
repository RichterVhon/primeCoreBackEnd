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
        Schema::create('warehouse_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_listing_id')->constrained()->cascadeOnDelete();
            $table->string('application_of_cusa')->nullable();
            $table->float('apex')->nullable();
            $table->float('shoulder_height')->nullable();
            $table->float('dimensions_of_the_entrance')->nullable();
            $table->integer('parking_allotment')->nullable();
            $table->integer('loading_bay')->nullable();
            $table->float('loading_bay_elevation')->nullable();
            $table->string('type_of_loading_bay')->nullable(); // Consider using an enum
            $table->string('loading_bay_vehicular_capacity')->nullable(); // Consider using an enum
            $table->string('electrical_load_capacity')->nullable(); // Consider using an enum
            $table->string('vehicle_capacity')->nullable(); // Consider using an enum
            $table->float('concrete_floor_strength')->nullable();
            $table->float('parking_rate_slot')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_specs');
    }
};
