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
        Schema::create('warehouse_lease_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_listing_id')
                ->constrained('warehouse_listings')
                ->onDelete('cascade');
            $table->float('rental_rate_sqm_for_open_area')->nullable();
            $table->float('rental_rate_sqm_for_covered_warehouse_area')->nullable();
            $table->float('rental_rate_sqm_for_office_area')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_lease_rates');
    }
};
