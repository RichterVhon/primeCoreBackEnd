<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommLotListingPropertyDetails extends Model
{
    protected $fillable = [
        'lot_area',
        'lot_shape',
        'frontage_width',
        'depth',
        'zoning_classification',
    ];

    protected $casts = [
        'lot_area' => 'float',
        //'lot_shape' => 'string', // can be enum later on in the project
        'frontage_width' => 'float',
        'depth' => 'float',
        //'zoning_classification' => 'string', // can be enum later on in the project
    ];

    public function commLotListing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\CommLotListing::class, 'comm_lot_listing_id');
    }
}
