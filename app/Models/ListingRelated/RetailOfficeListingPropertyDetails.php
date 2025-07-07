<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailOfficeListingPropertyDetails extends Model
{
    protected $fillable = [
        'floor_level',
        'unit_number',
        'leasable_size'
    ];

    protected $casts =[
        'leasable_size' => 'float',
    ];

    // Define any relationships if needed
    public function retailOfficeListing(): BelongsTo
    {
        return $this->belongsTo(RetailOfficeListing::class, 'retail_office_listing_id');
    }
}
