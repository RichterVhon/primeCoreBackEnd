<?php

namespace App\Models\ListingRelated;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\AcUnitType;
use App\Enums\BackupPowerType;
use App\Enums\FiberOpticCapability;
use App\Enums\ToiletType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeOtherDetailExtn extends Model {
    use SoftDeletes;
    use HasFactory;
    protected $table = 'office_other_details_extn';
protected $fillable= [
        'office_space_listing_id',
        'other_detail_id',
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
        //'electricity_meter',
        //'water_meter',
        'year_built',
        'total_floor_count',
        'other_remarks',
    ];

    protected $casts = [
        'ac_unit' => AcUnitType::class, // can be enum later on in the project
        'a/c_rate' => 'float',
        'cusa_on_ac' => 'float',
        'backup_power' => BackupPowerType::class, // can be enum later on in the project
        'fiber_optic_capability' => FiberOpticCapability::class, // can be enum later on in the project
        'passenger_elevators' => 'integer',
        'service_elevators' => 'integer',
        'private_toilet' => ToiletType::class, // can be enum later on in the project
        'common_toilet' => ToiletType::class, // can be enum later on in the project
        'total_floor_count' => 'integer',
    ];

    public function officeSpaceListing(): BelongsTo
    {
        return $this->belongsTo(OfficeSpaceListing::class, 'office_space_listing_id');
    }

    public function otherDetail(): BelongsTo
    {
        return $this->belongsTo(OtherDetailRelated\OtherDetail::class, 'other_detail_id');
    }

}
