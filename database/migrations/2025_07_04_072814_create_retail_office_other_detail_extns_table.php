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
        Schema::create('retail_office_other_detail_extns', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'retail_office_listing_id',
                'retail_office_listings',
                'fk_retail_other_detail_retail_listing'
            );
            SchemaHelpers::foreignKey(
                $table,
                'other_detail_id',
                'other_details',
                'fk_retail_other_detail_other_detail'
            );
            $table->string('pylon_availability')->nullable();
            $table->integer('total_floor_count')->nullable();
            $table->text('other_remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retail_office_other_detail_extns');
    }
};
