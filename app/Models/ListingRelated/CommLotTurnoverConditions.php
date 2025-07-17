<?php

namespace App\Models\ListingRelated;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\LotCondition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommLotTurnoverConditions extends Model {
    use SoftDeletes;
    use HasFactory;
    protected $fillable = [
        'comm_lot_listing_id',
        'lot_condition',
        'turnover_remarks',
    ];
    protected $casts = [
        'lot_condition' => LotCondition::class,
    ];

    public function commLotListing(): BelongsTo
    {
        return $this->belongsTo(CommLotListing::class, 'comm_lot_listing_id');
    }
}
