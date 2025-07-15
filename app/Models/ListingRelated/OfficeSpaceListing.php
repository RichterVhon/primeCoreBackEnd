<?php

namespace App\Models\ListingRelated;

use App\Traits\HasCustomId;
use App\Traits\HasSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OfficeSpaceListing extends Model
{
    use HasFactory;
    use HasSearch;
    use HasCustomId;

    protected $fillable = [
        'custom_id',
    ]; 

    public function customIdPrefix(): string
    {
        return 'OSP';
    }

     public static function searchableFields(): array
    {
        return array_merge([
            'custom_id',
            // 'account.email',
            // 'category.name'
        ], array_map(fn($field)=>"listing.$field", Listing::searchableFields()));
    }

    public static function filterableFields(): array
    {
        return array_merge([
            'PEZA_accredited'
        ], array_map(fn($field)=>"listing.$field", Listing::filterableFields()));
    }
    
    //para maging morph target ng Listing model
    public function listing(): MorphOne
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

    public function officeListingPropertyDetails(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\OfficeListingPropertyDetails::class, 'office_space_listing_id');
    }
}
