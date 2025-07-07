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
        Schema::create('comm_lot_listing_property_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comm_lot_listing_id')
                ->constrained('comm_lot_listings')
                ->onDelete('cascade');
            $table->float('lot_area')->nullable();
            $table->string('lot_shape')->nullable();
            $table->float('frontage_width')->nullable();
            $table->float('depth')->nullable();
            $table->string('zoning_classification')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comm_lot_listing_property_details');
    }
};
