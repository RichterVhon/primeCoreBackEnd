<?php

namespace App\Models\ListingRelated;

use App\Enums\TaxOnCusa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeLeaseTermsAndConditionsExtn extends Model
{

    use HasFactory;

    protected $fillable = [
        'tax_on_cusa' => TaxOnCusa::class,
        'cusa_on_parking',
        'parking_rate_slot',
        'parking_allotment',
        'office_space_listing_id',
    ];

    protected $casts = [
        //'tax_on_cusa' => 'string', can be enum later on in the project
        'cusa_on_parking' => 'float',
        'parking_rate_slot' => 'float',
        'parking_allotment' => 'integer',
    ];

    public function officeSpaceListing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\OfficeSpaceListing::class, 'office_space_listing_id');
    }

    public function leaseTermsAndConditions(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\LeaseTermsAndConditions::class, 'office_space_listing_id');
    }
}
