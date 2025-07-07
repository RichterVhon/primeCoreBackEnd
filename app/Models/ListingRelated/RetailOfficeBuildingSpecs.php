<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailOfficeBuildingSpecs extends Model
{
    //retailparking and retailmeasurements was merged into this model
    protected $fillable = [
        'PSI',
        'handover',
        'ceiling',
        'floor',
        'wall',
        'building_ops',
        'backup_power',
        'provision_for_genset',
        'security_system',
        'telecom_providers',
        'passenger_elevators',
        'service_elevators',
        'drainage_provision',
        'sewage_treatment_plan',
        'plumbing_provision',
        'toilet',
        'tenant_restrictions',
        'parking_rate_slot',
        'parking_rate_allotment',
        'floor_to_ceiling_height',
        'floor_to_floor_height',
        'mezzanine',
    ];

    protected $casts = [
        'PSI' => 'float',
        //'handover' => 'string', // can be enum later on in the project
        //'ceiling' => 'string',
        //'floor' => 'string',
        //'wall' => 'string',
        //'building_ops' => 'string',
        //'backup_power' => 'string', // can be enum later on in the project
        //'provision_for_genset' => 'string', // can be enum later on in the project
        //'security_system' => 'string',
        //'telecom_providers' => 'string',
        'passenger_elevators' => 'integer',
        'service_elevators' => 'integer',
        //'drainage_provision' => 'string', // can be enum later on in the project
        //'sewage_treatment_plan' => 'string', // can be enum later on in the project
        //'plumbing_provision' => 'string', // can be enum later on in the project
        //'toilet' => 'string', // can be enum later on in the project
        //'tenant_restrictions' => 'string', 
        'parking_rate_slot' => 'float', 
        'parking_rate_allotment' => 'float',        
        'floor_to_ceiling_height' => 'float',         
        'floor_to_floor_height' => 'float',
        'mezzanine' => 'float',
    ];

    public function retailOfficeListing(): BelongsTo
    {
        return $this->belongsTo(RetailOfficeListing::class, 'retail_office_listing_id');
    }
}
