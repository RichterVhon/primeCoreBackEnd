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
        Schema::create('warehouse_listings', function (Blueprint $table) {
            $table->id();
            $table->foreign('listing_id')
                  ->constrained('listings')
                  ->onDelete('cascade');
            $table->foreignId('lease_terms_id')
                ->constrained('lease_terms_and_conditions')
                ->onDelete('cascade');
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
