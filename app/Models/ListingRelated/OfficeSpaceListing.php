<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeSpaceListing extends Model
{
    use HasFactory;
    //para maging morph target ng Listing model
    public function listingMorph(): MorphOne
    {
        return $this->morphOne(\App\Models\ListingRelated\Listing::class, 'listable');
    }


    public function officeLeaseTermsAndConditionsExtn(): HasOne
    {
        return $this->hasOne(OfficeLeaseTermsAndConditionsExtn::class, 'office_space_listing_id');
    }

    public function officeTurnoverConditions(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\OfficeTurnoverConditions::class, 'office_space_listing_id');
    }

    public function officeSpecs(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\OfficeSpecs::class, 'office_space_listing_id');
    }

    public function officeOtherDetailExtn(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\OfficeOtherDetailExtn::class, 'office_space_listing_id');
    }

    public function officeListingPropDetails(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\OfficeListingPropertyDetails::class, 'office_space_listing_id');
    }
}
