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
            // 'account.email',
            // 'category.name'
        ], array_map(fn($field) => "listing.$field", Listing::searchableFields()));
    }

    public static function filterableFields(): array
    {
        return array_merge([
            'PEZA_accredited'
        ], array_map(fn($field) => "listing.$field", Listing::filterableFields()));
    }

    //para maging morph target ng Listing model
    public function listing(): MorphOne
    {
        return $this->morphOne(\App\Models\ListingRelated\Listing::class, 'listable')->withTrashed();
    }

    public function warehouseListingPropDetails(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\WarehouseListingPropDetails::class);
    }

    public function warehouseTurnoverConditions(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\WarehouseTurnoverConditions::class);
    }

    public function warehouseSpecs(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\WarehouseSpecs::class);
    }

    public function warehouseLeaseRate(): HasOne
    {
        return $this->hasOne(WarehouseLeaseRates::class);
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

}
