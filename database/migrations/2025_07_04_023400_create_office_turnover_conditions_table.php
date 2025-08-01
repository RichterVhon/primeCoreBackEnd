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
        Schema::create('office_turnover_conditions', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'office_space_listing_id',
                'office_space_listings',
                'fk_office_turnover_conditions_office_listing'
            );
            $table->string('handover')->nullable(); //make enum later on in the project
            $table->string('ceiling')->nullable();
            $table->string('wall')->nullable();
            $table->string('floor')->nullable();
            $table->string('turnover_remarks')->nullable();
            $table->timestamps();
        $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_turnover_conditions');
    }
};
