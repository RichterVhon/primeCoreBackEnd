<?php

namespace App\Models\ListingRelated;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OfficeListingPropertyDetails extends Model {
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'floor_level',
        'unit_number',
        'leasable_size',
        'office_space_listing_id',
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
