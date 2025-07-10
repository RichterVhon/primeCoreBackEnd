<?php

namespace App\Models\ListingRelated;

use App\Enums\LotShape;
use App\Enums\ZoningClassification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommLotListingPropertyDetails extends Model
{
    use HasFactory;
    protected $fillable = [
        'comm_lot_listing_id',
        'lot_area',
        'lot_shape',
        'frontage_width',
        'depth',
        'zoning_classification',
    ];

    protected $casts = [
        'lot_area' => 'float',
        'lot_shape' => LotShape::class, // can be enum later on in the project
        'frontage_width' => 'float',
        'depth' => 'float',
        'zoning_classification' => ZoningClassification::class, // can be enum later on in the project
    ];

    public function commLotListing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\CommLotListing::class, 'comm_lot_listing_id');
    }
}
