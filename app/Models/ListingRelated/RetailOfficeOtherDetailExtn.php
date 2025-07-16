<?php

namespace App\Models\ListingRelated;

use App\Enums\Pylonavailability;
use App\Traits\HasSearch;
use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RetailOfficeOtherDetailExtn extends Model
{

    use HasFactory;
    use HasSearch;
    
    protected $fillable = [
        'retail_office_listing_id',
        'other_detail_id',
        'pylon_availability',
        'total_floor_count',
        'other_remarks',
        //'retail_office_listing_id',
    ];

    protected $casts = [
        'pylon_availability' => Pylonavailability::class, // can be enum later on in the project
        'total_floor_count' => 'integer',
        'other_remarks' => 'string',
        //'retail_office_listing_id' => RetailOfficeListing::class,
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
