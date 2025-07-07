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
        Schema::create('retail_office_listing_property_details', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'retail_office_listing_id',
                'retail_office_listings',
                'fk_retail_listing_property_details_retail_listing'
            );

            $table->string('floor_level')->nullable();
            $table->string('unit_number')->nullable();
            $table->float('leasable_size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retail_office_listing_property_details');
    }
};
