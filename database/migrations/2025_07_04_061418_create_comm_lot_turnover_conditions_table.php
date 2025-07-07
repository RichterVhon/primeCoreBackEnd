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
        Schema::create('comm_lot_turnover_conditions', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'comm_lot_listing_id',
                'comm_lot_listings',
                'fk_comm_lot_turnover_conditions_comm_lot_listing'
            );
            $table->string('lot_condition'); // can be enum later on in the project
            $table->text('turnover_remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comm_lot_turnover_conditions');
    }
};
