<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_person',
        'position',
        'contact_number',
        'email_address',
        //'company_name', i'll comment this out bc lipat ko siya sa pivot table, since a contact can 
        // be represented by multiple companies??
        //di ko na lagay dito yung listing_id kasi hindi naman siya pagmamay-ari ng Contact model
    ];

    public function listings(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\ListingRelated\Listing::class)
            ->using(\App\Models\ContactListing::class)
            ->withTimestamps()
            ->withPivot('company');
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Account::class)
            ->using(\App\Models\AccountContact::class)
            ->withPivot('company_name') //, 'relationship_type')
            ->withTimestamps();

    }
}
