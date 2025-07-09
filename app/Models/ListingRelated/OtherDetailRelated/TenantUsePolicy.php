<?php

namespace App\Models\ListingRelated\OtherDetailRelated;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TenantUsePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'other_detail_id',
        'tenant_restrictions',
        'ideal_use',
    ];

    public function otherDetail(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\OtherDetailRelated\OtherDetail::class);
    }
}
