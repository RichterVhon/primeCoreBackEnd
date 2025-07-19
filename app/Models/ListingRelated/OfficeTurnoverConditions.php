<?php

namespace App\Models\ListingRelated;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\HandoverType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeTurnoverConditions extends Model {
    use SoftDeletes;
    use HasFactory;
    
    protected $fillable = [
        'handover', //enum
        'ceiling',
        'floor',
        'wall',
        'turnover_remarks',
        'office_space_listing_id',
    ];

    protected $casts = [
        'handover' => HandoverType::class

    ];

    public function officeSpaceListing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\OfficeSpaceListing::class, 'office_space_listing_id');
    }
}
