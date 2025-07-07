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
        Schema::create('office_listing_property_details', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'office_space_listing_id',
                'office_space_listings',
                'fk_office_listing_property_details_office_listing'
            );
            $table->string('floor_level')->nullable();
            $table->string('unit_number')->nullable();
            $table->string('leasable_size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_listing_property_details');
    }
};
