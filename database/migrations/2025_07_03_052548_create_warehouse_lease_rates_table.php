<?php

use App\Support\SchemaHelpers;
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
            SchemaHelpers::foreignKey(
                $table,
                'warehouse_listing_id',
                'warehouse_listings',
                'fk_warehouse_lease_rates_warehouse_listing'
            );
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
