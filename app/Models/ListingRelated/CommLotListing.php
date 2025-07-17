<?php

namespace App\Models\ListingRelated;
use App\Traits\HasSearch;

use App\Traits\HasCustomId;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommLotListing extends Model
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
        return 'CLT';
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
            'comm_lot_listing_property_details.lot_shape',
            'comm_lot_listing_property_details.zoning_classification',
            'comm_lot_turnover_conditions.lot_condition'
        ], array_map(fn($field) => "listing.$field", Listing::filterableFields()));
    }

    //para maging morph target ng Listing model
    public function listing(): MorphOne
    {
        return $this->morphOne(\App\Models\ListingRelated\Listing::class, 'listable');
    }

    public function commLotTurnoverConditions(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\CommLotTurnoverConditions::class, 'comm_lot_listing_id');
    }

    public function commLotListingPropertyDetails(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\CommLotListingPropertyDetails::class, 'comm_lot_listing_id');
    }

    protected static bool $deletionGuard = false;

    protected static function booted()
    {
        static::deleting(function ($commlot) {
            if (self::$deletionGuard) {
                Log::info("🛑 Skipping CommLot deletion due to guard");
                return;
            }

            Log::info("⛔ Deleting CommLotListing ID {$commlot->id}");
            self::$deletionGuard = true;

            // Delete CommLot components
            $commlot->commLotTurnoverConditions?->delete();
            Log::info("✔ Deleted commLotTurnoverConditions");

            $commlot->commLotListingPropertyDetails?->delete();
            Log::info("✔ Deleted commLotListingPropertyDetails");

            // Delete associated Listing
            if ($commlot->listing && !$commlot->listing->trashed()) {
                Log::info("🔁 Deleting linked Listing ID {$commlot->listing->id}");
                $commlot->listing->delete();
            }

            self::$deletionGuard = false;
        });
    }

    protected static bool $restorationGuard = false;

    public function restoreCascade(): void
    {
        if (self::$restorationGuard) {
            Log::info("🛑 Skipping CommLotListing restoration due to guard");
            return;
        }

        Log::info("🔄 Restoring CommLotListing ID {$this->id}");
        self::$restorationGuard = true;

        $this->restore();

        $this->commLotTurnoverConditions?->restore();
        Log::info("✔ Restored commLotTurnoverConditions");

        $this->commLotListingPropertyDetails?->restore();
        Log::info("✔ Restored commLotListingPropertyDetails");

        if ($this->listing && $this->listing->trashed()) {
            Log::info("🔁 Restoring linked Listing ID {$this->listing->id}");
            $this->listing->restoreCascade(); // assumes Listing has restoreCascade()
        }

        self::$restorationGuard = false;
    }
}

