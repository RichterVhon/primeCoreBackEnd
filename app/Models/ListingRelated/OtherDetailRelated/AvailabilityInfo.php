<?php

namespace App\Models\ListingRelated\OtherDetailRelated;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AvailabilityInfo extends Model {
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'other_detail_id',
        'date_of_availability', // date
        // copilot suggested date available from and to? review later on 
        'date_of_availability_remarks', // text 
    ];

    protected $casts = [
        'date_of_availability' => 'date', // cast to date type
        // 'date_available_from' => 'date', // if you decide to add this field
        // 'date_available_to' => 'date', // if you decide to add this field
    ];

    public function otherDetail(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\OtherDetailRelated\OtherDetail::class);
    }
}
