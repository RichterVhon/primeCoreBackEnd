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
        Schema::create('office_specs', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'office_space_listing_id',
                'office_space_listings',
                'fk_office_specs_office_listing'
            );
            $table->string('density_ratio')->nullable(); 
            $table->float('floor_to_ceiling_height')->nullable();
            $table->float('floor_to_floor')->nullable();
            $table->string('accreditation')->nullable(); 
            $table->string('certification')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_specs');
    }
};
