<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaHelpers;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lease_documents', function (Blueprint $table) {
            $table->id();
            // made the foreign key creation reusable
            // using SchemaHelpers to avoid repetition
            SchemaHelpers::foreignKey(
                $table, 
                'listing_id', 
                'listings', 
                'fk_lease_documents_listing'
            );
            $table->string('photos_and_property_documents_link')->nullable();
            $table->string('professional_fee_structure')->nullable();
            $table->timestamps();
        $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lease_documents');
    }
};
