<?php

namespace App\Models\ListingRelated;

use App\Enums\YesNo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseListingPropDetails extends Model
{   
    use HasFactory;
    protected $fillable =[
        'warehouse_listing_id',
        'unit_number',
        'leasable_warehouse_area_on_the_ground_floor',
        'leasable_warehouse_area_on_the_upper_floor',
        'leasable_office_area',
        'total_leasable_area',
        'total_open_area',
        'total_leasable_area_open_covered',
        'FDAS',
    ];

    protected $casts = [
        'warehouse_listing_id',
        'leasable_warehouse_area_on_the_ground_floor' => 'float',
        'leasable_warehouse_area_on_the_upper_floor' => 'float',
        'leasable_office_area' => 'float',
        'total_leasable_area' => 'float',
        'total_open_area' => 'float',
        'total_leasable_area_open_covered' => 'float',
        'FDAS' => YesNo::class,
    ];

    public function warehouseListing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\WarehouseListing::class, 'warehouse_listing_id');    
    }

}
