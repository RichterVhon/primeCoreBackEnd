<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailOfficeOtherDetailExtn extends Model
{
    protected $fillable = [
        'pylon_availability',
        'total_floor_count',
        'other_remarks'
    ];

    protected $casts = [
        //'pylon_availability' => 'string', // can be enum later on in the project
        'total_floor_count' => 'integer',
        //'other_remarks' => 'string',
    ];

    public function retailOfficeListing(): BelongsTo
    {
        return $this->belongsTo(RetailOfficeListing::class, 'retail_office_listing_id');
    }

    public function otherDetail(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\OtherDetailRelated\OtherDetail::class, 'other_detail_id');
    }
}
