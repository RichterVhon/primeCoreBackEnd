<?php

namespace App\Models\ListingRelated;
use App\Traits\HasSearch;

use App\Traits\HasCustomId;
use App\Enums\AccreditationType;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IndLotListing extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasCustomId;
    use HasSearch;


    protected $fillable = [
        'custom_id',
        'PEZA_accredited',
    ];
    protected $casts = [
        'PEZA_accredited' => AccreditationType::class,
    ];

    public function customIdPrefix(): string
    {
        return 'ILT';
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
            'PEZA_accredited',
            'ind_listing_property_details.lot_shape',
            'ind_listing_property_details.zoning_classification',
            'ind_listing_property_details.offering',
            'ind_lot_turnover_conditions.lot_condition'
        ], array_map(fn($field) => "listing.$field", Listing::filterableFields()));
    }
    //para maging morph target ng Listing model
    public function listing(): MorphOne
    {
        return $this->morphOne(\App\Models\ListingRelated\Listing::class, 'listable');
    }

    public function indLotLeaseRates(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\IndLotLeaseRates::class, 'ind_lot_listing_id');
    }

    public function indLotTurnoverConditions(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\IndLotTurnoverConditions::class, 'ind_lot_listing_id');
    }

    public function indLotListingPropertyDetails(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\IndLotListingPropertyDetails::class, 'ind_lot_listing_id');
    }
    protected static bool $deletionGuard = false;

    protected static function booted()
    {
        static::deleting(function ($indlot) {
            if (self::$deletionGuard) {
                Log::info("🛑 Skipping IndLot deletion due to guard");
                return;
            }

            Log::info("⛔ Deleting IndLotListing ID {$indlot->id}");
            self::$deletionGuard = true;

            // Delete IndLot components
            $indlot->indLotLeaseRates?->delete();
            Log::info("✔ Deleted indLotLeaseRates");

            $indlot->indLotTurnoverConditions?->delete();
            Log::info("✔ Deleted indLotTurnoverConditions");

            $indlot->indLotListingPropertyDetails?->delete();
            Log::info("✔ Deleted indLotListingPropertyDetails");

            // Delete associated Listing
            if ($indlot->listing && !$indlot->listing->trashed()) {
                Log::info("🔁 Deleting linked Listing ID {$indlot->listing->id}");
                $indlot->listing->delete();
            }

            self::$deletionGuard = false;
        });
    }
}
