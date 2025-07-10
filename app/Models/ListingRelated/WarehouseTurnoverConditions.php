<?php

namespace App\Models\ListingRelated;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseTurnoverConditions extends Model
{
    use HasFactory;
    protected $fillable =[
        'warehouse_listing_id',
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
