<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasSearch
{
    // public function scopeSearch(Builder $query, string $term, array $columns): Builder
    // {
    //     return $query->where(function ($q) use ($term, $columns) {
    //         foreach ($columns as $column) {
    //             if (str_contains($column, '.')) {
    //                 [$relation, $field] = explode('.', $column);
    //                 $q->orWhereHas($relation, function ($relQuery) use ($field, $term) {
    //                     $relQuery->where($field, 'like', "%{$term}%");
    //                 });
    //             } else {
    //                 $q->orWhere($column, 'like', "%{$term}%");
    //             }
    //         }
    //     });
    // }

    public function scopeSearch(Builder $query, string $term, array $columns): Builder
    {
        return $query->where(function ($q) use ($term, $columns) {
            foreach ($columns as $column) {
                if (str_contains($column, '.')) {
                    $segments = explode('.', $column);
                    $field = array_pop($segments);
                    $relationPath = implode('.', $segments);

                    $q->orWhereHas($relationPath, function ($relQuery) use ($field, $term) {
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
        //dump('Incoming filters:', $filters);
        $model = $query->getModel();
        $relationMethods = collect(get_class_methods($model))
            ->filter(fn($method) => method_exists($model, $method) && is_callable([$model, $method]))
            ->toArray();

        foreach ($filters as $key => $value) {
            if (is_array($value)) {
                if (isset($value['from']) || isset($value['to'])) {
                    if (isset($value['from'])) {
                        $this->applyFilter($query, $key, '>=', $value['from'], $relationMethods);
                    }
                    if (isset($value['to'])) {
                        $this->applyFilter($query, $key, '<=', $value['to'], $relationMethods);
                    }
                } else {
                    $this->applyFilter($query, $key, 'IN', $value, $relationMethods);
                }
            } else {
                $this->applyFilter($query, $key, '=', $value, $relationMethods);
            }
        }

        return $query;
    }

    private function applyFilter(Builder $query, string $key, string $operator, mixed $value, array $relationMethods): void
    {
        if (str_contains($key, '.')) {
            $segments = explode('.', $key);
            $field = array_pop($segments);
            $relationPath = implode('.', $segments); // e.g. listing.location

            // ✅ Dump outside the closure to avoid scope issues
            //dump("→ Filtering: {$relationPath}.{$field} {$operator} " . json_encode($value));

            $query->whereHas($relationPath, function ($q) use ($field, $operator, $value) {
                if ($operator === 'IN') {
                    $q->whereIn($field, $value);
                } else {
                    $q->where($field, $operator, $value);
                }
            });

            return;
        }

        // Direct field on the model
        //dump("→ Filtering: {$key} {$operator} " . json_encode($value));

        if ($operator === 'IN') {
            $query->whereIn($key, $value);
        } else {
            $query->where($key, $operator, $value);
        }
    }





}
