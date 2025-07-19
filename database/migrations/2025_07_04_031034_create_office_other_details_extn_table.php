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
        Schema::create('office_other_details_extn', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'office_space_listing_id',
                'office_space_listings',
                'fk_office_other_details_office_listing'
            );
            SchemaHelpers::foreignKey(
                $table,
                'other_detail_id',
                'other_details',
                'fk_office_other_details_other_detail'
            );
            $table->string('a/c_unit')->nullable();
            $table->string('a/c_type')->nullable();
            $table->decimal('a/c_rate', 10, 2)->nullable();
            $table->decimal('cusa_on_ac', 10, 2)->nullable();
            $table->string('building_ops')->nullable();
            $table->string('backup_power')->nullable();
            $table->string('fiber_optic_capability')->nullable();
            $table->string('telecom_providers')->nullable();
            $table->integer('passenger_elevators')->nullable();
            $table->integer('service_elevators')->nullable();
            $table->string('private_toilet')->nullable();
            $table->string('common_toilet')->nullable();
            $table->string('tenant_restrictions')->nullable();
            $table->string('year_built')->nullable();
            $table->integer('total_floor_count')->nullable();
            $table->text('other_remarks')->nullable();
            $table->timestamps();
        $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_other_details_extn');
    }
};
