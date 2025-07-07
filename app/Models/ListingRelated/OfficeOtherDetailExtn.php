<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeOtherDetailExtn extends Model
{
    protected $fillable= [
        'a/c_unit',
        'a/c_type',
        'a/c_rate',
        'cusa_on_ac',
        'building_ops',
        'backup_power',
        'fiber_optic_capability',
        'telecom_providers',
        'passenger_elevators',
        'service_elevators',
        'private_toilet',
        'common_toilet',
        'tenant_restrictions',
        //'electricity_meter', already exists in general otherdertails
        //'water_meter', already exists in general otherdertails
        'year_built',
        'total_floor_count',
        'other_remarks',
    ];

    protected $casts = [
        //'ac_unit' => 'string', // can be enum later on in the project
        'a/c_rate' => 'float',
        'cusa_on_ac' => 'float',
        //'backup_power' => 'float', // can be enum later on in the project
        //'fiber_optic_capability' => 'string', // can be enum later on in the project
        'passenger_elevators' => 'integer',
        'service_elevators' => 'integer',
        //'private_toilet' => 'string', // can be enum later on in the project
        //'common_toilet' => 'string', // can be enum later on in the project
        'total_floor_count' => 'integer',
    ];

    public function officeSpaceListing(): BelongsTo
    {
        return $this->belongsTo(OfficeSpaceListing::class, 'office_space_listing_id');
    }

    public function otherDetail(): BelongsTo
    {
        return $this->belongsTo(OtherDetailRelated\OtherDetail::class, 'office_space_listing_id');
    }
}
