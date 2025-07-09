<?php

namespace App\Models\ListingRelated\OtherDetailRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OtherDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'listing_id',
        'electricity_meter',
        'water_meter',
        'year_built',
    ];

    protected $casts = [
        'electricity_meter' => 'boolean', //can be yes/no, create enum later on sa project na
        'water_meter' => 'boolean',
        'year_built' => 'integer',
    ];

    public function availabilityInfo(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\OtherDetailRelated\AvailabilityInfo::class);
    }

    public function tenantUsePolicy(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\OtherDetailRelated\TenantUsePolicy::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\Listing::class);
    }

    public function officeOtherDetailExtn(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\OfficeOtherDetailExtn::class);
    }

    public function retailOfficeOtherDetailExtn(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\RetailOfficeOtherDetailExtn::class);
    }
}
