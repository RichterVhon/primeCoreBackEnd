<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Model;

class WarehouseTurnoverConditions extends Model
{
    protected $fillable =[
        'flooring_turonver',
        'ceiling_turnover',
        'wall_turnover',
        'turnover_remarks',
    ];

    public function warehouseListing()
    {
        return $this->belongsTo(WarehouseListing::class, 'warehouse_listing_id');
    }   

}
