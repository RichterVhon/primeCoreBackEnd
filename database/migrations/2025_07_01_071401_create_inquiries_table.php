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
            // Agent who logged the inquiry
            SchemaHelpers::foreignKey(
                $table,
                'agent_id',
                'accounts',
                'fk_inquiries_agent'
            );

            // Client/viewer the inquiry is for
            SchemaHelpers::foreignKey(
                $table,
                'client_id',
                'accounts',
                'fk_inquiries_client'
            );
            SchemaHelpers::foreignKey(
                $table,
                'listing_id',
                'listings',
                'fk_inquiries_listing'
            );
            //$table->string('agent_in_charge')->nullable();
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
