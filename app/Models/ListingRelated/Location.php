<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    //use locations for table name
    protected $fillable = [
        'province',
        'city',
        'district',
        'google_coordinates_latitude',
        'google_coordinates_longitude',
        'exact_address',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\Listing::class);
    }
}
