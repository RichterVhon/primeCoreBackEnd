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
        Schema::create('other_details', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'listing_id',
                'listings',
                'fk_other_details_listing'
            );
            $table->boolean('electricity_meter')->default(false); // can be yes/no,
            $table->boolean('water_meter')->default(false);
            $table->integer('year_built')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_details');
    }
};
