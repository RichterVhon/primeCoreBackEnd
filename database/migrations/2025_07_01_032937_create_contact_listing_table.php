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
        Schema::create('contact_listing', function (Blueprint $table) {
            $table->id();
            $table->unique(['contact_id', 'listing_id']);
            SchemaHelpers::foreignKey(
                $table,
                'contact_id',
                'contacts',
                'fk_contact_listing_contact'
            );
            SchemaHelpers::foreignKey(
                $table,
                'listing_id',
                'listings',
                'fk_contact_listing_listing'
            );
            $table->string('company')->nullable(); // Company name associated with the contact in
            $table->timestamps();
        $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_listing');
    }
};
