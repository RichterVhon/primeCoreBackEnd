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
        Schema::create('tenant_use_policies', function (Blueprint $table) {
            $table->id();
            SchemaHelpers::foreignKey(
                $table,
                'other_detail_id',
                'other_details',
                'fk_tenant_use_policies_other_details'
            );
            $table->text('tenant_restrictions')->nullable(); // text, can be null
            $table->text('ideal_use')->nullable(); // text, can be null
            $table->timestamps();
        $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_use_policies');
    }
};
