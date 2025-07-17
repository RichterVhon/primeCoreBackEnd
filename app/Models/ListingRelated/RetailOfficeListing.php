<?php

namespace App\Models\ListingRelated;
use App\Traits\HasSearch;

use App\Traits\HasCustomId;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RetailOfficeListing extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasCustomId;
    use HasSearch;

    protected $fillable = [
        'custom_id',
    ];

    public function customIdPrefix(): string
    {
        return 'RSP';
    }

    public static function searchableFields(): array
    {
        return array_merge([
            'custom_id',
            // 'account.email',
            // 'category.name'
        ], array_map(fn($field) => "listing.$field", Listing::searchableFields()));
    }

    public static function filterableFields(): array
    {
        return array_merge([
            //'PEZA_accredited'
        ], array_map(fn($field) => "listing.$field", Listing::filterableFields()));
    }

    //para maging morph target ng Listing model
    public function listing(): MorphOne
    {
        return $this->morphOne(\App\Models\ListingRelated\Listing::class, 'listable')->withTrashed();
    }

    public function retailOfficeTurnoverConditions(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\RetailOfficeTurnoverConditions::class, 'retail_office_listing_id')->withTrashed();
    }

    public function retailOfficeListingPropertyDetails(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\RetailOfficeListingPropertyDetails::class, 'retail_office_listing_id')->withTrashed();
    }

    public function retailOfficeBuildingSpecs(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\RetailOfficeBuildingSpecs::class, 'retail_office_listing_id')->withTrashed();
    }

    public function retailOfficeOtherDetailExtn(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\RetailOfficeOtherDetailExtn::class, 'retail_office_listing_id')->withTrashed();
    }

    protected static bool $deletionGuard = false;

    protected static function booted()
    {
        static::deleting(function ($retailoffice) {
            if (self::$deletionGuard) {
                Log::info("🛑 Skipping RetailOffice deletion due to guard");
                return;
            }

            Log::info("⛔ Deleting RetailOfficeListing ID {$retailoffice->id}");
            self::$deletionGuard = true;

            // Delete RetailOffice components
            $retailoffice->retailOfficeTurnoverConditions?->delete();
            Log::info("✔ Deleted retailOfficeTurnoverConditions");

            $retailoffice->retailOfficeListingPropertyDetails?->delete();
            Log::info("✔ Deleted retailOfficeListingPropertyDetails");

            $retailoffice->retailOfficeBuildingSpecs?->delete();
            Log::info("✔ Deleted retailOfficeBuildingSpecs");

            $retailoffice->retailOfficeOtherDetailExtn?->delete();
            Log::info("✔ Deleted retailOfficeOtherDetailExtn");

            // Delete associated Listing
            if ($retailoffice->listing && !$retailoffice->listing->trashed()) {
                Log::info("🔁 Deleting linked Listing ID {$retailoffice->listing->id}");
                $retailoffice->listing->delete();
            }

            self::$deletionGuard = false;
        });
    }

    protected static bool $restorationGuard = false;

    public function restoreCascade(): void
    {
        if (self::$restorationGuard) {
            Log::info("🛑 Skipping RetailOfficeListing restoration due to guard");
            return;
        }

        Log::info("🔄 Restoring RetailOfficeListing ID {$this->id}");
        self::$restorationGuard = true;

        $this->restore();

        $this->retailOfficeTurnoverConditions?->restore();
        Log::info("✔ Restored retailOfficeTurnoverConditions");

        $this->retailOfficeListingPropertyDetails?->restore();
        Log::info("✔ Restored retailOfficeListingPropertyDetails");

        $this->retailOfficeBuildingSpecs?->restore();
        Log::info("✔ Restored retailOfficeBuildingSpecs");

        $this->retailOfficeOtherDetailExtn?->restore();
        Log::info("✔ Restored retailOfficeOtherDetailExtn");

        if ($this->listing && $this->listing->trashed()) {
            Log::info("🔁 Restoring linked Listing ID {$this->listing->id}");
            $this->listing->restoreCascade(); // assumes Listing has restoreCascade()
        }

        self::$restorationGuard = false;
    }
}
