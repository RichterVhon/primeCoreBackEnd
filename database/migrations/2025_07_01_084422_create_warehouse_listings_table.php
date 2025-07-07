<?php

use App\Support\SchemaHelpers;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouse_listings', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'listing_id',
                'listings',
                'fk_warehouse_listings_listing'
            );
            $table->boolean('PEZA_accredited')->default(false); // Assuming this is a field for Warehouse listings
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_listings');
    }
};
