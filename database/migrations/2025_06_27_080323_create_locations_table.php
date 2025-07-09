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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'listing_id', 
                'listings', 
                'fk_locations_listing'
            );
            $table->string('province');
            $table->string('city');
            $table->string('district');
            $table->decimal('google_coordinates_latitude', 11, 8);
            $table->decimal('google_coordinates_longitude', 11, 8);
            $table->string('exact_address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
