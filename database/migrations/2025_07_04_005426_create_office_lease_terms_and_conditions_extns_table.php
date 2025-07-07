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
        Schema::create('office_lease_terms_and_conditions_extns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_space_listing_id')
                ->constrained('office_space_listings')
                ->onDelete('cascade');
            $table->foreignId('lease_terms_and_conditions_id')
                ->constrained('lease_terms_and_conditions')
                ->onDelete('cascade');
            $table->string('tax_on_cusa'); // Assuming this is a string, can be enum later on in the project
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
