<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndLotTurnoverConditions extends Model
{
    protected $fillable = [
        'lot_condition', //can be enum later on in the project
        'turnover_remarks',
    ];


    public function indLotListing(): BelongsTo
    {
        return $this->belongsTo(IndLotListing::class, 'ind_lot_listing_id');
    }
}
