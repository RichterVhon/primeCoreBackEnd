<?php

namespace App\Models\ListingRelated;


use App\Models\WarehouseLeaseRate;

use App\Enums\AccreditationType;

use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WarehouseListing extends Model
{
    use HasFactory;
    use HasCustomId;

    protected $fillable = [
        'custom_id',
        'PEZA_accredited',
    ];  
    protected $casts = [
        'PEZA_accredited' => AccreditationType::class,
    ];

    public function customIdPrefix(): string
    {
        return 'WA';
    }


    //para maging morph target ng Listing model
    public function listing(): MorphOne
    {
        return $this->morphOne(\App\Models\ListingRelated\Listing::class, 'listable');
    }

    public function warehouseListingPropDetails(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\WarehouseListingPropDetails::class);
    }

    public function warehouseTurnoverConditions(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\WarehouseTurnoverConditions::class);
    }

    public function warehouseSpecs(): HasOne
    {
        return $this->hasOne(\App\Models\ListingRelated\WarehouseSpecs::class);
    }

    public function warehouseLeaseRate(): HasOne
{
    return $this->hasOne(WarehouseLeaseRates::class);
}


}
