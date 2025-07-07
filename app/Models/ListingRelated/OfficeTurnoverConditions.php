<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeTurnoverConditions extends Model
{
    protected $fillable = [
        'handover', //make enum later on in the project
        'ceiling',
        'wall',
        'turnover_remarks'
    ];

    public function officeSpaceListing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\OfficeSpaceListing::class, 'office_space_listing_id');
    }
}
