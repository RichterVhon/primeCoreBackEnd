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
        Schema::create('lease_terms_and_conditions', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'listing_id',
                'listings',
                'fk_lease_terms_and_conditions_listing'
            );
            $table->float('monthly_rate')->nullable();
            $table->float('cusa_sqm')->nullable();
            $table->float('security_deposit')->nullable();
            $table->float('advance_rental')->nullable();
            $table->boolean('application_of_advance')->default(false);
            $table->integer('min_lease')->nullable();
            $table->integer('max_lease')->nullable();
            $table->float('escalation_rate')->nullable();
            $table->string('escalation_frequency')->nullable(); // Can be enum later on
            $table->date('escalation_effectivity')->nullable(); // Can be enum later on
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lease_terms_and_conditions');
    }
};
