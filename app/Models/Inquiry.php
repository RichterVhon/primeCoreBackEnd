<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\InquiryStatus;
use App\Traits\HasSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inquiry extends Model {
    use SoftDeletes;
    use HasFactory;
    use HasSearch;


    protected $fillable = [
        'agent_id',
        'client_id',
        'listing_id',
        //'agent_in_charge', should be accessible via accounts->name 
        'status',
        'message',
        'viewing_schedule'
    ];


    protected $casts = [
        'status' => InquiryStatus::class,
        'viewing_schedule' => 'datetime', // assuming this is a date-time field
    ];

    public static function searchableFields(): array
    {
        return [
            'message',
            'status',
            'agent.name',
            'agent.email',
            'client.name',
            'client.email',
            'listing.name',
        ];
    }

    public static function filterableFields(): array
    {
        return [
            'status',
            'agent_id',
            'client_id',
            'listing_id',
            'agent.created_at',
            'client.created_at',
            'listing.status',
        ];
    }


    public function account(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Account::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ListingRelated\Listing::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'agent_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'client_id');
    }
}
