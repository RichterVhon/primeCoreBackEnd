<?php

namespace App\Models\ListingRelated;
use App\Traits\HasSearch;


use App\Traits\HasCustomId;

use App\Enums\AccreditationType;

use App\Models\WarehouseLeaseRate;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WarehouseListing extends Model
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
        return 'WA';
    }

    public static function searchableFields(): array
    {
        return array_merge([
            'custom_id',
            'warehouseListingPropDetails.unit_number',
            'warehouseTurnoverConditions.turnover_remarks',
            'warehouseSpecs.type_of_loading_bay',
            'warehouseSpecs.loading_bay_vehicular_capacity',
            'warehouseSpecs.electrical_load_capacity',
            'warehouseSpecs.vehicle_capacity',
        ], array_map(fn($field) => "listing.$field", Listing::searchableFields()));
    }

    public static function filterableFields(): array
    {
        return array_merge([
            'PEZA_accredited',

            // Property Details
            'warehouseListingPropDetails.leasable_warehouse_area_on_the_ground_floor',
            'warehouseListingPropDetails.leasable_warehouse_area_on_the_upper_floor',
            'warehouseListingPropDetails.leasable_office_area',
            'warehouseListingPropDetails.total_leasable_area',
            'warehouseListingPropDetails.total_open_area',
            'warehouseListingPropDetails.total_leasable_area_open_covered',
            'warehouseListingPropDetails.FDAS',

            // Turnover Conditions
            'warehouseTurnoverConditions.ceiling_turnover',
            'warehouseTurnoverConditions.wall_turnover',

            // Specs
            'warehouseSpecs.application_of_cusa',
            'warehouseSpecs.apex',
            'warehouseSpecs.shoulder_height',
            'warehouseSpecs.dimensions_of_the_entrance',
            'warehouseSpecs.parking_allotment',
            'warehouseSpecs.loading_bay',
            'warehouseSpecs.loading_bay_elevation',
            'warehouseSpecs.type_of_loading_bay',
            'warehouseSpecs.loading_bay_vehicular_capacity',
            'warehouseSpecs.electrical_load_capacity',
            'warehouseSpecs.vehicle_capacity',
            'warehouseSpecs.concrete_floor_strength',
            'warehouseSpecs.parking_rate_slot',

            // Lease Rates
            'warehouseLeaseRate.rental_rate_sqm_for_open_area',
            'warehouseLeaseRate.rental_rate_sqm_for_covered_warehouse_area',
            'warehouseLeaseRate.rental_rate_sqm_for_office_area',
        ], array_map(fn($field) => "listing.$field", Listing::filterableFields()));
    }

    //para maging morph target ng Listing model
    public function listing(): MorphOne
    {
        return $this->morphOne(\App\Models\ListingRelated\Listing::class, 'listable')->withTrashed();
    }

    public function warehouseListingPropDetails(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\WarehouseListingPropDetails::class)->withTrashed();
    }

    public function warehouseTurnoverConditions(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\WarehouseTurnoverConditions::class)->withTrashed();
    }

    public function warehouseSpecs(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\WarehouseSpecs::class)->withTrashed();
    }

    public function warehouseLeaseRate(): HasOne
    {
        return $this->hasOne(WarehouseLeaseRates::class)->withTrashed();
    }

    protected static bool $deletionGuard = false;

    protected static function booted()
    {
        static::deleting(function ($warehouse) {
            if (self::$deletionGuard) {
                Log::info("🛑 Skipping Warehouse deletion due to guard");
                return;
            }

            Log::info("⛔ Deleting WarehouseListing ID {$warehouse->id}");
            self::$deletionGuard = true;

            // Delete Warehouse components
            $warehouse->warehouseListingPropDetails?->delete();
            Log::info("✔ Deleted warehouseListingPropDetails");

            $warehouse->warehouseTurnoverConditions?->delete();
            Log::info("✔ Deleted warehouseTurnoverConditions");

            $warehouse->warehouseSpecs?->delete();
            Log::info("✔ Deleted warehouseSpecs");

            $warehouse->warehouseLeaseRate?->delete();
            Log::info("✔ Deleted warehouseLeaseRate");

            // Delete linked Listing
            if ($warehouse->listing && !$warehouse->listing->trashed()) {
                Log::info("🔁 Deleting Listing ID " . $warehouse->listing->id);
                $warehouse->listing->delete();
            }

            self::$deletionGuard = false;
        });
    }

    protected static bool $restorationGuard = false;

    public function restoreCascade(): void
    {
        if (self::$restorationGuard) {
            Log::info("🛑 Skipping WarehouseListing restoration due to guard");
            return;
        }

        Log::info("🔄 Restoring WarehouseListing ID {$this->id}");
        self::$restorationGuard = true;

        $this->restore();

        $this->warehouseSpecs?->restore();
        $this->warehouseLeaseRate?->restore();
        $this->warehouseListingPropDetails?->restore();
        $this->warehouseTurnoverConditions?->restore();

        if ($this->listing && $this->listing->trashed()) {
            $this->listing->restoreCascade();
        }

        self::$restorationGuard = false;
    }


}
