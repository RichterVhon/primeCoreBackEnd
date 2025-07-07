<?php
namespace App\Support;

use Illuminate\Database\Schema\Blueprint;

class SchemaHelpers
{
    public static function foreignKey(
        Blueprint $table,
        string $column,
        string $referenceTable,
        string $constraintName,
        bool $nullable = false
    ): void {
        $columnDef = $table->unsignedBigInteger($column);

        if ($nullable) {
            $columnDef->nullable();
        }

        $table->foreign($column, $constraintName)
              ->references('id')
              ->on($referenceTable)
              ->onDelete('cascade');
    }
}