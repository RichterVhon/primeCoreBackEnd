<?php

namespace App\Models\ListingRelated;

use App\Traits\HasCustomId;
use App\Traits\HasSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RetailOfficeListing extends Model
{
    use HasFactory;
    use HasCustomId;
    use HasSearch;
    
    protected $fillable = [
        'custom_id',
    ]; 

    public function customIdPrefix(): string
    {
        return 'RSP';
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
            //'PEZA_accredited'
        ], array_map(fn($field)=>"listing.$field", Listing::filterableFields()));
    }
    
    //para maging morph target ng Listing model
    public function listing(): MorphOne
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

    public function retailOfficeOtherDetailExtn(): HasOne{
        return $this->hasOne(\App\Models\ListingRelated\RetailOfficeOtherDetailExtn::class, 'retail_office_listing_id');
    }

    
}
