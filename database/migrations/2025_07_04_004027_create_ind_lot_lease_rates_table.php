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
        Schema::create('ind_lot_lease_rates', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'ind_lot_listing_id',
                'ind_lot_listings',
                'fk_ind_lot_lease_rates_ind_lot_listing'
            );
            $table->decimal('rental_rate_sqm_for_open_area', 10, 2)->nullable();
            $table->decimal('rental_rate_sqm_for_covered_area', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ind_lot_lease_rates');
    }
};
