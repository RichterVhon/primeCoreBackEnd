<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\InquiryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inquiry extends Model {
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'account_id', // Foreign key to the account
        'listing_id', // Foreign key to the listing
        'status', // can be ENUM: 'pending', 'responded', 'archived'
        'message',
        'viewing_schedule',
    ];

    protected $casts = [
        'status' => InquiryStatus::class, 
        'viewing_schedule' => 'datetime', // assuming this is a date-time field
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Account::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\Listing::class);
    }
}
