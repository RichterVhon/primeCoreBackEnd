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
        Schema::create('retail_office_listings', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'listing_id',
                'listings',
                'fk_retail_office_listings_listing'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retail_office_listings');
    }
};
