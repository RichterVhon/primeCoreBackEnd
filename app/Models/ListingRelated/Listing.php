<?php

namespace App\Models\ListingRelated;
use App\Traits\HasSearch;

use App\Enums\AuthorityType;
use App\Enums\ListingStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Listing extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasSearch;
    // hindi ko na lagay here yung name ng table, basta rapat ang name ng table ng listing ay "listings" (matic na sa laravel kasi yun)

    protected $fillable = [
        'account_id',
        'status',
        'date_last_updated',
        'date_uploaded',
        'project_name',
        'property_name',
        'bd_incharge',
        'authority_type',
        'bd_securing_remarks',
        'listable_id',
        'custom_listable_id',
        'listable_type', // ← this line is critical
        // gawan ng helper function para sa mga remarks? para HasRemarks nalang

    ];

    protected $casts = [
        'status' => ListingStatus::class,
        'authority_type' => AuthorityType::class,
    ];

    public static function searchableFields(): array
    {
        return [
            // Listing fields
            'project_name',
            'property_name',
            'bd_incharge',
            'authority_type',
            'listable_type',
            'custom_listable_id',

            // Location
            'location.province',
            'location.city',
            'location.district',
            'location.exact_address',

            // Lease Terms
            'leaseTermsAndConditions.remarks',

            // Contacts (via pivot)
            'contacts.contact_person',
            'contacts.position',
            'contacts.email_address',
            'contacts.contact_number',
            'contacts.company',
        ];
    }


    public static function filterableFields(): array
    {
        return [
            // Listing fields
            'status',
            'date_uploaded',
            'date_last_updated',

            // Location
            'location.province',
            'location.city',
            'location.district',

            // Other Details
            'otherDetail.electricity_meter',
            'otherDetail.water_meter',
            'otherDetail.year_built',

            // Lease Terms
            'leaseTermsAndConditions.monthly_rate',
            'leaseTermsAndConditions.cusa_sqm',
            'leaseTermsAndConditions.security_deposit',
            'leaseTermsAndConditions.advance_rental',
            'leaseTermsAndConditions.application_of_advance',
            'leaseTermsAndConditions.min_lease',
            'leaseTermsAndConditions.max_lease',
            'leaseTermsAndConditions.escalation_rate',
            'leaseTermsAndConditions.escalation_frequency',
            'leaseTermsAndConditions.escalation_effectivity',

            // Contacts (via pivot)
            'contacts.position',
            'contacts.company',
        ];
    }



    public function location(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\Location::class)->withTrashed();
    }

    public function leaseDocument(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\LeaseDocument::class)->withTrashed();
    }

    public function otherDetail(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\OtherDetailRelated\OtherDetail::class)->withTrashed();
    }


    public function leaseTermsAndConditions(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\LeaseTermsAndConditions::class)->withTrashed();
    }


    public function inquiries(): HasMany
    {
        return $this->hasMany(\App\Models\Inquiry::class)->withTrashed();
    }


    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Contact::class)
            ->using(\App\Models\ContactListing::class)
            ->withTimestamps()
            ->withPivot('company', 'deleted_at') // include deleted_at for clarity
            ->withTrashed(); // ensures soft-deleted pivot rows are accessible
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Account::class);
    }

    public function listable(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    protected static bool $deletionGuard = false;
    protected static function booted()
    {
        static::creating(function ($listing) {
            $listing->load('listable');
            $listing->custom_listable_id = $listing->listable->custom_id ?? 'UNSET';
        });

        static::deleting(function ($listing) {
            if (self::$deletionGuard) {
                Log::info("🛑 Skipping Listing deletion due to guard");
                return;
            }

            Log::info("⛔ Deleting Listing ID {$listing->id}");
            self::$deletionGuard = true;

            // Delete Listing components
            $listing->location?->delete();
            Log::info("✔ Deleted location");

            $listing->leaseDocument?->delete();
            Log::info("✔ Deleted leaseDocument");

            $listing->leaseTermsAndConditions?->delete();
            Log::info("✔ Deleted leaseTerms");

            $listing->otherDetail?->delete();
            Log::info("✔ Deleted otherDetail");

            $listing->inquiries()->each(function ($inquiry) {
                $inquiry->delete();
            });
            Log::info("✔ Deleted inquiries");

            $listing->contacts()->each(function ($contact) use ($listing) {
                $listing->contacts()->updateExistingPivot($contact->id, ['deleted_at' => now()]);
            });
            Log::info("✔ Detached contacts");

            // Delete morph target (e.g. WarehouseListing)
            if ($listing->listable && !$listing->listable->trashed()) {
                Log::info("🔁 Deleting listable of type " . get_class($listing->listable));
                $listing->listable->delete();
            }

            self::$deletionGuard = false;
        });
    }

    protected static bool $restorationGuard = false;

    public function restoreCascade(): void
    {
        if (self::$restorationGuard) {
            Log::info("🛑 Skipping Listing restoration due to guard");
            return;
        }

        Log::info("🔄 Restoring Listing ID {$this->id}");
        self::$restorationGuard = true;

        $this->restore();

        $this->location?->restore();
        $this->leaseDocument?->restore();
        $this->leaseTermsAndConditions?->restore();
        $this->otherDetail?->restore();
        $this->inquiries()->withTrashed()->get()->each->restore();


        // Restore soft-deleted pivot rows for contacts
        $this->contacts()->withTrashed()->get()->each(function ($contact) {
            $this->contacts()->updateExistingPivot($contact->id, ['deleted_at' => null]);
        });

        // Restore polymorphic listable
        if ($this->listable && $this->listable->trashed()) {
            $this->listable->restoreCascade();
        }

        self::$restorationGuard = false;
    }

}
