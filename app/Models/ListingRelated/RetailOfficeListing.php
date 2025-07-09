<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RetailOfficeListing extends Model
{

    use HasFactory;
    //para maging morph target ng Listing model
    public function listingMorph(): MorphOne
    {
        return $this->morphOne(\App\Models\ListingRelated\Listing::class, 'listable');
    }

    public function retailOfficeTurnoverConditions(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\RetailOfficeTurnoverConditions::class, 'retail_office_listing_id');
    }

    public function retailOfficeListingPropertyDetails(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\RetailOfficeListingPropertyDetails::class, 'retail_office_listing_id');
    }

    public function retailOfficeBuildingSpecs(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\RetailOfficeBuildingSpecs::class, 'retail_office_listing_id');
    }

    
}
