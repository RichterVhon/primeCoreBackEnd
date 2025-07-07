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
        Schema::create('retail_office_other_detail_extns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retail_office_listing_id')
                ->constrained('retail_office_listings')
                ->onDelete('cascade');
            $table->foreignId('other_detail_id')
                ->constrained('other_details')
                ->onDelete('cascade');
            $table->string('pylon_availability')->nullable();
            $table->integer('total_floor_count')->nullable();
            $table->text('other_remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retail_office_other_detail_extns');
    }
};
