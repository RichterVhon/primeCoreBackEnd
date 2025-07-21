<?php

namespace App\Models;
use App\Traits\HasSearch;

use Illuminate\Support\Facades\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasSearch;
    protected $fillable = [
        'contact_person',
        'position',
        'contact_number',
        'email_address',
        //'company_name', i'll comment this out bc lipat ko siya sa pivot table, since a contact can 
        // be represented by multiple companies??
        //di ko na lagay dito yung listing_id kasi hindi naman siya pagmamay-ari ng Contact model
    ];

    public static function searchableFields(): array
    {
        return [
            'contact_person',
            'position',
            'contact_number',
            'email_address',
        ];
    }

    public static function filterableFields(): array
    {
        return [
            //
        ];
    }

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
            ->withPivot('company_name', 'deleted_at') //, 'relationship_type')
            ->withTimestamps();
    }



    protected static function booted()
    {
        static::created(function (Contact $contact) {
            $user = Auth::user();

            if ($user) {
                $contact->accounts()->syncWithoutDetaching([
                    $user->id => ['company_name' => null]
                ]);
            }
        });
    }


}
