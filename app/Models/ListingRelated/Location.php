<?php

namespace App\Models\ListingRelated;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\Province;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model {
    use SoftDeletes;
    use HasFactory;

    //use locations for table name
    protected $fillable = [
        'listing_id',
        'province',
        'city',
        'district',
        'google_coordinates_latitude',
        'google_coordinates_longitude',
        'exact_address',
    ];

    protected $casts = [
        'province' => Province::class,
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\Listing::class);
    }
}
