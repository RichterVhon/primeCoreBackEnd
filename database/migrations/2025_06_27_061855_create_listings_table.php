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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'account_id',
                'accounts',
                'fk_listings_account'
            );
            $table->string('status')->default('draft');
            $table->date('date_uploaded')->nullable();
            $table->date('date_last_updated')->nullable();
            $table->string('project_name')->nullable();
            $table->string('property_name')->nullable();
            $table->string('bd_incharge')->nullable();
            $table->string('authority_type')->nullable();
            $table->text('bd_securing_remarks')->nullable();

            $table->morphs('listable'); // This will create 'listable_id' and 'listable_type' columns for polymorphic relations
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
