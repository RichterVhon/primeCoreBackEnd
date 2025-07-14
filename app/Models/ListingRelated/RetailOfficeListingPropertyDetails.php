<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailOfficeListingPropertyDetails extends Model
{

    use HasFactory;

    protected $fillable = [
        'floor_level',
        'unit_number',
        'leasable_size',
        'retail_office_listing_id',
    ];

    protected $casts =[
        'leasable_size' => 'float',
        'retail_office_listing_id'=> RetailOfficeListing::class,
    ];

    // Define any relationships if needed
    public function retailOfficeListing(): BelongsTo
    {
        return $this->belongsTo(RetailOfficeListing::class, 'retail_office_listing_id');
    }
}
