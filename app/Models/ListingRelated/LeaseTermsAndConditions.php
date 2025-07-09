<?php

namespace App\Models\ListingRelated;

use App\Enums\EscalationFrequency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaseTermsAndConditions extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'monthly_rate',
        'cusa_sqm',
        'security_deposit',
        'advance_rental',
        'application_of_advance',
        'min_lease',
        'max_lease',
        'escalation_rate',
        'escalation_frequency',
        'escalation_effectivity',
        'remarks',
    ];

    protected $casts = [
        'monthly_rate' => 'float',
        'cusa_sqm' => 'float',
        'security_deposit' => 'float',
        'advance_rental' => 'float',
        'application_of_advance' => 'boolean',
        'min_lease' => 'integer',
        'max_lease' => 'integer',
        'escalation_rate' => 'float',
        'escalation_frequency' => EscalationFrequency::class,
    ];


    public function listing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\Listing::class);
    }
    
    public function officeLeaseTermsAndConditionsExtn(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\OfficeLeaseTermsAndConditionsExtn::class, 'lease_terms_id');
    }


}
