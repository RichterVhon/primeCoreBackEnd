<?php

namespace App\Models\ListingRelated;
use App\Traits\HasSearch;

use App\Traits\HasCustomId;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasListingValidationRules;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OfficeSpaceListing extends Model {
    use SoftDeletes;
    use HasFactory;
    use HasSearch;
    use HasCustomId;
    use HasListingValidationRules;

    protected $fillable = [
        'custom_id',
    ];

    public function customIdPrefix(): string
    {
        return 'OSP';
    }

    public static function searchableFields(): array
    {
        return array_merge([
            'custom_id',
            'OfficeSpecs.accreditation',
            'OfficeSpecs.certification',
            'OfficeTurnoverConditions.handover',
            // 'account.email',
            // 'category.name'
        ], array_map(fn($field) => "listing.$field", Listing::searchableFields()));
    }

    public static function filterableFields(): array
    {
        return array_merge([
            //
        ], array_map(fn($field) => "listing.$field", Listing::filterableFields()));
    }

    //para maging morph target ng Listing model
    public function listing(): MorphOne
    {
        return $this->morphOne(\App\Models\ListingRelated\Listing::class, 'listable')->withTrashed();
    }


    public function OfficeLeaseTermsAndConditionsExtn(): HasOne
    {
        return $this->hasOne(OfficeLeaseTermsAndConditionsExtn::class, 'office_space_listing_id')->withTrashed();
    }

    public function OfficeTurnoverConditions(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\OfficeTurnoverConditions::class, 'office_space_listing_id')->withTrashed();
    }

    public function OfficeSpecs(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\OfficeSpecs::class, 'office_space_listing_id')->withTrashed();
    }

    public function OfficeOtherDetailExtn(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\OfficeOtherDetailExtn::class, 'office_space_listing_id')->withTrashed();
    }

    public function OfficeListingPropertyDetails(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\OfficeListingPropertyDetails::class, 'office_space_listing_id')->withTrashed();
    }

    protected static bool $deletionGuard = false;

    protected static function booted()
    {
        static::deleting(function ($officespace) {
            if (self::$deletionGuard) {
                Log::info("🛑 Skipping OfficeSpace deletion due to guard");
                return;
            }

            Log::info("⛔ Deleting OfficeSpaceListing ID {$officespace->id}");
            self::$deletionGuard = true;

            // Delete OfficeSpace components
            $officespace->OfficeLeaseTermsAndConditionsExtn?->delete();
            Log::info("✔ Deleted OfficeLeaseTermsAndConditionsExtn");

            $officespace->OfficeTurnoverConditions?->delete();
            Log::info("✔ Deleted OfficeTurnoverConditions");

            $officespace->OfficeSpecs?->delete();
            Log::info("✔ Deleted OfficeSpecs");

            $officespace->OfficeOtherDetailExtn?->delete();
            Log::info("✔ Deleted OfficeOtherDetailExtn");

            $officespace->OfficeListingPropertyDetails?->delete();
            Log::info("✔ Deleted OfficeListingPropertyDetails");

            // Delete associated Listing
            if ($officespace->listing && !$officespace->listing->trashed()) {
                Log::info("🔁 Deleting linked Listing ID {$officespace->listing->id}");
                $officespace->listing->delete();
            }

            self::$deletionGuard = false;
        });
    }
        protected static bool $restorationGuard = false;

    public function restoreCascade(): void
    {
        if (self::$restorationGuard) {
            Log::info("🛑 Skipping OfficeSpaceListing restoration due to guard");
            return;
        }

        Log::info("🔄 Restoring OfficeSpaceListing ID {$this->id}");
        self::$restorationGuard = true;

        $this->restore();

        $this->OfficeLeaseTermsAndConditionsExtn?->restore();
        Log::info("✔ Restored OfficeLeaseTermsAndConditionsExtn");

        $this->OfficeTurnoverConditions?->restore();
        Log::info("✔ Restored OfficeTurnoverConditions");

        $this->OfficeSpecs?->restore();
        Log::info("✔ Restored OfficeSpecs");

        $this->OfficeOtherDetailExtn?->restore();
        Log::info("✔ Restored OfficeOtherDetailExtn");

        $this->OfficeListingPropertyDetails?->restore();
        Log::info("✔ Restored OfficeListingPropertyDetails");

        if ($this->listing && $this->listing->trashed()) {
            Log::info("🔁 Restoring linked Listing ID {$this->listing->id}");
            $this->listing->restoreCascade(); // assumes Listing has restoreCascade()
        }

        self::$restorationGuard = false;
    }
}
