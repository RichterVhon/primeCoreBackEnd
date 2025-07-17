<?php

namespace App\Models\ListingRelated;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\ElectricalLoadCapacity;
use App\Enums\LoadingBayVehicularCapacity;
use App\Enums\TypeOfLoadingBay;
use App\Enums\VehicleCapacity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WarehouseSpecs extends Model {
    use SoftDeletes;
    use HasFactory;


    protected $fillable = [
        'warehouse_listing_id',
        'application_of_cusa',
        'apex',
        'shoulder_height',
        'dimensions_of_the_entrance',
        'parking_allotment',
        'loading_bay',
        'loading_bay_elevation',
        'type_of_loading_bay',
        'loading_bay_vehicular_capacity',
        'electrical_load_capacity',
        'vehicle_capacity',
        'concrete_floor_strength',
        'parking_rate_slot'
    ];
    protected $casts = [
        //'warehouse_listing_id',
        'apex' => 'float',
        'shoulder_height' => 'float',
        //'dimensions_of_the_entrance' => 'array', string kasi ata to e?
        'parking_allotment' => 'integer',
        'loading_bay' => 'integer',
        'loading_bay_elevation' => 'float', //pero sabi sa datasheet int daw?
        'type_of_loading_bay' => TypeOfLoadingBay::class, //make this an enum?
        'loading_bay_vehicular_capacity' => VehicleCapacity::class, //make this an enum?
        'electrical_load_capacity' => ElectricalLoadCapacity::class, //make this an enum?
        'vehicle_capacity' => VehicleCapacity::class, //make this an enum?
        'concrete_floor_strength' => 'float',
        'parking_rate_slot' => 'float'
    ];


    public function warehouseListing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\WarehouseListing::class, 'warehouse_listing_id');
    }


}