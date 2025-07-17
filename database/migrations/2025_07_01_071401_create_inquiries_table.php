<?php

use App\Support\SchemaHelpers;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'account_id',
                'accounts',
                'fk_inquiries_account'
            );
            SchemaHelpers::foreignKey(
                $table,
                'listing_id',
                'listings',
                'fk_inquiries_listing'
            );
            $table->string('status'); 
            $table->text('message');
            $table->dateTime('viewing_schedule')->nullable(); 
            $table->timestamps();
        $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
