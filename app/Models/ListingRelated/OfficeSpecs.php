<?php

namespace App\Models\ListingRelated;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\AccreditationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeSpecs extends Model {
    use SoftDeletes;

    use HasFactory;

    protected $fillable = [
        'density_ratio',
        'floor_to_ceiling_height',
        'floor_to_floor',
        'accreditation',
        'certification',
        'office_space_listing_id',
    ];

    protected $casts = [
        'floor_to_ceiling_height' => 'float',
        'floor_to_floor' => 'float',
        'accreditation' => AccreditationType::class, // can be enum later on in the project
    ];

    public function officeSpaceListing()
    {
        return $this->belongsTo(OfficeSpaceListing::class, 'office_space_listing_id');
    }
}
