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
        Schema::create('availability_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('other_detail_id')->constrained()->onDelete('cascade');
            $table->date('date_of_availability'); // date
            $table->text('date_of_availability_remarks')->nullable(); // text, can be null
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_infos');
    }
};
