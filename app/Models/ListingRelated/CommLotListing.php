<?php

namespace App\Models\ListingRelated;

use App\Traits\HasCustomId;
use App\Traits\HasSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class CommLotListing extends Model
{
    use HasFactory;
    use HasCustomId;
    use HasSearch;

    protected $fillable = [
        'custom_id',
    ]; 
    public function customIdPrefix(): string
    {
        return 'CLT';
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
            'comm_lot_listing_property_details.lot_shape',
            'comm_lot_listing_property_details.zoning_classification',
            'comm_lot_turnover_conditions.lot_condition'
        ], array_map(fn($field)=>"listing.$field", Listing::filterableFields()));
    }
    
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
