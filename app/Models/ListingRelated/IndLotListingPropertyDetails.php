<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndLotListingPropertyDetails extends Model
{
    protected $fillable = [
        'lot_area',
        'lot_shape',
        'frontage_width',
        'depth',
        'zoning_classification',
        'offering'
    ];

    protected $casts = [
        'lot_area' => 'float',
        //'lot_shape' => 'string', // can be enum later on in the project
        'frontage_width' => 'float',
        'depth' => 'float',
        //'zoning_classification' => 'string', // can be enum later on in the project
        //'offering' => 'string', // can be enum later on in the project
    ];

    public function indLotListing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\IndLotListing::class, 'ind_lot_listing_id');
    }
}
