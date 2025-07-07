<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IndLotListing extends Model
{

    protected $fillable = [
        'PEZA_accredited',
    ];  
    protected $casts = [
        'PEZA_accredited' => 'boolean',
    ];
    //para ma-reference yung listing_id sa warehouse_listings table
    public function listing(): BelongsTo
    {
        return $this->belongsTo('App\Models\Listing');
    }

    //para maging morph target ng Listing model
    public function listingMorph(): MorphOne
    {
        return $this->morphOne(\App\Models\ListingRelated\Listing::class, 'listable');
    }

    public function indLotLeaseRates(): HasOne
    {
        return $this->hasOne(\App\Models\IndLotLeaseRates::class, 'ind_lot_listing_id');
    }

    public function indLotTurnoverConditions(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\IndLotTurnoverConditions::class, 'ind_lot_listing_id');
    }

    public function indLotListingPropertyDetails(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\IndLotListingPropertyDetails::class, 'ind_lot_listing_id');
    }
}
