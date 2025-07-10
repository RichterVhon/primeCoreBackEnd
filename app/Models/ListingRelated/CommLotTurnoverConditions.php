<?php

namespace App\Models\ListingRelated;

use App\Enums\LotCondition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommLotTurnoverConditions extends Model
{
    use HasFactory;
    protected $fillable = [
        'comm_lot_listing_id',
        'lot_condition' => LotCondition::class,
        'turnover_remarks',
    ];

    public function commLotListing(): BelongsTo
    {
        return $this->belongsTo(CommLotListing::class, 'comm_lot_listing_id');
    }
}
