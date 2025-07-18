<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasSearch
{
    public function scopeSearch(Builder $query, string $term, array $columns): Builder
    {
        return $query->where(function ($q) use ($term, $columns) {
            foreach ($columns as $column) {
                if (str_contains($column, '.')) {
                    [$relation, $field] = explode('.', $column);
                    $q->orWhereHas($relation, function ($relQuery) use ($field, $term) {
                        $relQuery->where($field, 'like', "%{$term}%");
                    });
                } else {
                    $q->orWhere($column, 'like', "%{$term}%");
                }
            }
        });
    }

public function scopeApplyFilters(Builder $query, array $filters): Builder
{
    foreach ($filters as $key => $value) {
        if (!is_null($value)) {
            if (str_contains($key, '.')) {
                $segments = explode('.', $key);
                $field = array_pop($segments); // Get the column name
                $relation = implode('.', $segments); // Rebuild the relationship path

                $query->whereHas($relation, function ($q) use ($field, $value) {
                    $q->where($field, '=', $value);
                });
            } else {
                $query->where($key, '=', $value);
            }
        }
    }

    return $query;
}

}