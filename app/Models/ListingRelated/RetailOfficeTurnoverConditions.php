<?php

namespace App\Models\ListingRelated;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetailOfficeTurnoverConditions extends Model {
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'turnover_remarks',
        'frontage_turnover',
        'retail_office_listing_id',
    ];

    protected $casts = [
        'turnover_remarks' => 'string',
        'frontage_turnover' => 'float',
        //'retail_office_listing_id' => RetailOfficeListing::class,
    ];

    public function retailOfficeListing(): BelongsTo
    {
        return $this->belongsTo(RetailOfficeListing::class, 'retail_office_listing_id');
    }
}
