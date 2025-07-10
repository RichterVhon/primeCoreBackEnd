<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IndLotLeaseRates extends Model
{
    use HasFactory;
    protected $fillable = [
        'ind_lot_listing_id',
        'rental_rate_sqm_for_open_area',
        'rental_rate_sqm_for_covered_area',
    ];

    protected $casts = [
        'rental_rate_sqm_for_open_area' => 'float',
        'rental_rate_sqm_for_covered_area' => 'float',
    ];

    public function indLotListing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\IndLotListing::class, 'ind_lot_listing_id');
    }
}
