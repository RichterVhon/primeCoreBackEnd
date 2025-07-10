<?php

namespace App\Models\ListingRelated;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\LotShape;
use App\Enums\Offering;
use App\Enums\ZoningClassification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class IndLotListingPropertyDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'ind_lot_listing_id',
        'lot_area',
        'lot_shape',
        'frontage_width',
        'depth',
        'zoning_classification',
        'offering'
    ];

    protected $casts = [
        'lot_area' => 'float',
        'lot_shape' => LotShape::class,
        'frontage_width' => 'float',
        'depth' => 'float',
        'zoning_classification' => ZoningClassification::class, 
        'offering' => Offering::class, 
    ];

    public function indLotListing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\IndLotListing::class, 'ind_lot_listing_id');
    }
}
