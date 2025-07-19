<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\HasSearch;
use App\Enums\AccountRole;
use App\Enums\AccountStatus;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAccountValidationRules;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Account extends Authenticatable
{
    use HasFactory;
    use HasSearch;
    use HasAccountValidationRules;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // can be enum later on in the project
        'status',
        //'company name',

    ];

    protected $casts = [
        'password' => 'hashed', // Laravel will automatically hash the password
        'role' => AccountRole::class, //this is enum, yay
        'status' => 'boolean', // can be active/inactive, create enum later on in the project


        // 'status' => AccountStatus::class, // can be active/inactive, create enum later on in the project
    ];

    public static function searchableFields(): array
    {
        return [
            'name',
            'email',
            'role',
            'status', // can be enum later on in the projectp
            // 'account.email',
            // 'category.name'
        ];
    }

    public static function filterableFields(): array
    {
        return [
            'role',
            'status',
            'created_at',
            'updated_at'
        ];
    }

    public function listings(): HasMany
    {
        return $this->hasMany(\App\Models\ListingRelated\Listing::class);
    }

    // For inquiries where the account is acting as a client
    public function clientInquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'client_id');
    }

    // For inquiries where the account is acting as an agent
    public function agentInquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'agent_id');
    }


    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Contact::class)
            ->using(\App\Models\AccountContact::class)
            ->withPivot('company_name') //, 'relationship_type')
            ->withTimestamps();
    }

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::deleting(function ($account) {
    //         if ($account->isForceDeleting()) {
    //             $account->contacts()->detach(); // hard delete = detach
    //         } else {
    //             // Soft delete pivot records instead of detaching
    //             $account->contacts()->each(function ($contact) use ($account) {
    //                 $account->contacts()->updateExistingPivot($contact->id, [
    //                     'deleted_at' => now()
    //                 ]);
    //             });
    //         }
    //     });
    // }



}
