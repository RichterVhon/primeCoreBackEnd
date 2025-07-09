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
