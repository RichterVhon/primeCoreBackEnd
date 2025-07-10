<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IndLotTurnoverConditions extends Model
{
    use HasFactory;
    protected $fillable = [
        'lot_condition', //can be enum later on in the project
        'turnover_remarks',
    ];


    public function indLotListing(): BelongsTo
    {
        return $this->belongsTo(IndLotListing::class, 'ind_lot_listing_id');
    }
}
