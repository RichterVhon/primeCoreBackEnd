<?php

namespace App\Traits;

trait HasCustomId
{
    protected static function bootHasCustomId()
    {
        static::creating(function ($model) {
            $prefix = $model->customIdPrefix(); // Defined in the model that uses the trait

            $lastId = $model::where('custom_id', 'like', "{$prefix}%")
                ->orderBy('custom_id', 'desc')
                ->value('custom_id');

            $nextNum = $lastId
                ? ((int) substr($lastId, strlen($prefix)) + 1)
                : 1;

            $model->custom_id = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        });
    }

    /**
     * This must be implemented in the model using the trait.
     */
    abstract public function customIdPrefix(): string;
}
