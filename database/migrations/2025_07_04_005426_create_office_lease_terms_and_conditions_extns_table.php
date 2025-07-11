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
        Schema::create('office_lease_terms_and_conditions_extns', function (Blueprint $table) {
            $table->id();

            SchemaHelpers::foreignKey(
                $table,
                'office_space_listing_id',
                'office_space_listings',
                'fk_office_lease_terms_office_listing'
            );

            SchemaHelpers::foreignKey(
                $table,
                'lease_terms_and_conditions_id',
                'lease_terms_and_conditions',
                'fk_office_lease_terms_lease_terms'
            );

            $table->string('tax_on_cusa')-> nullable();
            $table->decimal('cusa_on_parking', 10, 2); 
            $table->decimal('parking_rate_slot', 10, 2);
            $table->integer('parking_allotment')->default(0); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_lease_terms_and_conditions_extns');
    }
};
