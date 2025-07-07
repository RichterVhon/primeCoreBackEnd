<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommLotTurnoverConditions extends Model
{
    protected $fillable = [
        'lot_condition', // can be enum later on in the project
        'turnover_remarks',
    ];

    public function commLotListing(): BelongsTo
    {
        return $this->belongsTo(CommLotListing::class, 'comm_lot_listing_id');
    }
}
