<?php

namespace App\Models\ListingRelated;

use App\Enums\ListingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Listing extends Model
{
    use HasFactory;
    
    // hindi ko na lagay here yung name ng table, basta rapat ang name ng table ng listing ay "listings" (matic na sa laravel kasi yun)

    protected $fillable = [
        'account_id',
        'status', 
        'date_last_updated',
        'date_uploaded',
        'project_name',
        'property_name',
        'bd_incharge',
        'authority_type', //enum di ko pa nagagawa to (since faker)
        'bd_securing_remarks',
        'listable_id',
        'listable_type', // ← this line is critical
        // gawan ng helper function para sa mga remarks? para HasRemarks nalang

    ];

    protected $casts = [
        'status'=> ListingStatus::class,
    ];


    public function location(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\Location::class);
    }

    public function leaseDocument(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\LeaseDocument::class);
    }

    public function otherDetail(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\OtherDetailRelated\OtherDetail::class);
    }

    
    public function leaseTermsAndConditions(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\LeaseTermsAndConditions::class);
    }
    

    public function inquiries(): HasMany
    {
        return $this->hasMany(\App\Models\Inquiry::class);
    }


    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Contact::class)
            ->using(\App\Models\ContactListing::class)
            ->withTimestamps()
            ->withPivot('company');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Account::class);
    }

    public function listable(): MorphTo 
    {
        return $this->morphTo();
    }
}
