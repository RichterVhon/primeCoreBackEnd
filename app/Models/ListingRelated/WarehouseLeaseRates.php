<?php

namespace App\Models\ListingRelated;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseLeaseRates extends Model {
    use SoftDeletes;

    use HasFactory;
    //attach ko to sa warehouse specs, since ayun yung parang tumatayong lease terms and conditions extension ng warehouse listing
    // tas eto yung extension ng warehouse specs 
    protected $fillable = [
        'rental_rate_sqm_for_open_area',
        'rental_rate_sqm_for_covered_warehouse_area',
        'rental_rate_sqm_for_office_area',
    ];

    protected $casts = [
        'rental_rate_sqm_for_open_area' => 'float',
        'rental_rate_sqm_for_covered_warehouse_area' => 'float',
        'rental_rate_sqm_for_office_area' => 'float',
    ];

    public function warehouseListing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\WarehouseListing::class, 'warehouse_listing_id');
    }

}
