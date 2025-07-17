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
        $model = $query->getModel();
        $relationMethods = collect(get_class_methods($model))
            ->filter(fn($method) => method_exists($model, $method) && is_callable([$model, $method]))
            ->toArray();

        foreach ($filters as $key => $value) {
            if (!is_null($value)) {
                if (str_contains($key, '_')) {
                    $segments = explode('_', $key);
                    $relation = array_shift($segments);
                    $field = implode('_', $segments);

                    if (in_array($relation, $relationMethods)) {
                        //dump("→ whereHas: {$relation}, field: {$field}, value: {$value}");

                        $query->whereHas($relation, function ($q) use ($field, $value) {
                            //dump("→ SQL inside whereHas: {$field} = {$value}");
                            $q->where($field, '=', $value);
                        });

                        continue;
                    }
                }

                //dump("→ direct where: {$key} = {$value}");
                $query->where($key, '=', $value);
            }
        }

        return $query;
    }






}
