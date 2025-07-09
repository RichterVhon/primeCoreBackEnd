<?php

namespace App\Support;
use Illuminate\Database\Eloquent\Relations\Relation;

class MorphHelper
{
    public static function getMorphAlias(string $modelClass): string
    {
        return array_search($modelClass, Relation::morphMap()) ?: $modelClass;
    }
}
