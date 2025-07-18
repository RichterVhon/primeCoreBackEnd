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
        Schema::create('retail_office_building_specs', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'retail_office_listing_id',
                'retail_office_listings',
                'fk_retail_office_building_specs_retail_listing'        
            );
            $table->float('PSI')->nullable(); 
            $table->string('handover')->nullable();
            $table->string('ceiling')->nullable();
            $table->string('wall')->nullable();
            $table->string('floor')->nullable();
            $table->string('building_ops')->nullable();
            $table->string('backup_power')->nullable();
            $table->string('provision_for_genset')->nullable();
            $table->string('security_system')->nullable();
            $table->string('telecom_providers')->nullable();
            $table->integer('passenger_elevators')->nullable();
            $table->integer('service_elevators')->nullable();
            $table->string('drainage_provision')->nullable();
            $table->string('sewage_treatment_plan')->nullable();
            $table->string('plumbing_provision')->nullable();
            $table->string('toilet')->nullable();
            $table->string('tenant_restrictions')->nullable();
            $table->float('parking_rate_slot')->nullable();
            $table->float('parking_rate_allotment')->nullable();
            $table->float('floor_to_ceiling_height')->nullable();
            $table->float('floor_to_floor_height')->nullable();
            $table->float('mezzanine')->nullable();
            $table->timestamps();
        $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retail_office_building_specs');
    }
};
