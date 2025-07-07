<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeListingPropertyDetails extends Model
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
    public function officeSpaceListing(): BelongsTo
    {
        return $this->belongsTo(OfficeSpaceListing::class, 'office_space_listing_id');
    }
}
