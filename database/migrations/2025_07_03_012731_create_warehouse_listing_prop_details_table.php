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
        Schema::create('warehouse_listing_prop_details', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'warehouse_listing_id',
                'warehouse_listings',
                'fk_warehouse_listing_prop_details_warehouse_listing'
            );
            $table->string('unit_number')->nullable();
            $table->float('leasable_warehouse_area_on_the_ground_floor')->nullable();
            $table->float('leasable_warehouse_area_on_the_upper_floor')->nullable();
            $table->float('leasable_office_area')->nullable();
            $table->float('total_leasable_area')->nullable();
            $table->float('total_open_area')->nullable();
            $table->float('total_leasable_area_open_covered')->nullable();
            $table->string('FDAS')->nullable(); 
            $table->timestamps();
        $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_listing_prop_details');
    }
};
