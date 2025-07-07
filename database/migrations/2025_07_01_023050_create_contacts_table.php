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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('contact_person');
            $table->string('position')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email_address')->nullable();
            //$table->string('company_name')->nullable(); // Commented out because the company name will be stored in the pivot table, as a contact can be represented by multiple companies.
            //listing reference is in pivot table, wala here
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
