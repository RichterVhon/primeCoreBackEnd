<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WarehouseSpecs extends Model
{
    use HasFactory;


    protected $fillable = [
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
        'apex' => 'float',
        'shoulder_height' => 'float',
        //'dimensions_of_the_entrance' => 'array', string kasi ata to e?
        'parking_allotment' => 'integer',
        'loading_bay' => 'integer',
        'loading_bay_elevation' => 'float', //pero sabi sa datasheet int daw?
        //'type_of_loading_bay' => 'string', //make this an enum?
        //'loading_bay_vehicular_capacity' => 'string', //make this an enum?
        //'electrical_load_capacity' => 'string', //make this an enum?
        //'vehicle_capacity' => 'string', //make this an enum?
        'concrete_floor_strength' => 'float',
        'parking_rate_slot' => 'float'
    ];

    /*
    public function leaseTermsAndConditions(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\LeaseTermsAndConditions::class);
    }
    
    tanggal ko na to since wala nmn siya masyado kinalaman sa leaseterms */

    public function warehouseLeaseRates(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\WarehouseLeaseRates::class, 'warehouse_specs_id');
    }


}