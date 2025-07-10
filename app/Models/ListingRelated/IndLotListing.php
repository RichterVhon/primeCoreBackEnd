<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IndLotListing extends Model
{
    use HasFactory;
    protected $fillable = [
        'PEZA_accredited',
    ];  
    protected $casts = [
        'PEZA_accredited' => 'boolean',
    ];

    //para maging morph target ng Listing model
    public function listingMorph(): MorphOne
    {
        return $this->morphOne(\App\Models\ListingRelated\Listing::class, 'listable');
    }

    public function indLotLeaseRates(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\IndLotLeaseRates::class, 'ind_lot_listing_id');
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
