<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommLotListing extends Model
{

    use HasFactory;
    //para maging morph target ng Listing model
    public function listing(): MorphOne
    {
        return $this->morphOne(\App\Models\ListingRelated\Listing::class, 'listable');
    }

    public function commLotTurnoverConditions(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\CommLotTurnoverConditions::class, 'comm_lot_listing_id');
    }

    public function commLotListingPropertyDetails(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\CommLotListingPropertyDetails::class, 'comm_lot_listing_id');
    }   
}
