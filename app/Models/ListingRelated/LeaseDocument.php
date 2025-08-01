<?php

namespace App\Models\ListingRelated;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaseDocument extends Model {
    use SoftDeletes;
    use HasFactory;
    //use lease_documents for table name
    protected $fillable = [
        'listing_id',
        'photos_and_property_documents_link',
        'professional_fee_structure',
    ];

    protected $casts = [
        'professional_fee_structure' => 'array', // JSON kasi gianwa ko here, can be text tho?
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\Listing::class);
    }
}
