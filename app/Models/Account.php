<?php

namespace App\Models;

use App\Enums\AccountRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Account extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // can be enum later on in the project
        'status'
    ];

    protected $casts = [
        'password' => 'hashed', // Laravel will automatically hash the password
        'role' => AccountRole::class, //this is enum, yay
        'status' => 'boolean', // can be active/inactive, create enum later on in the project
    ];

    public function listings(): HasMany
    {
        return $this->hasMany(\App\Models\ListingRelated\Listing::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(\App\Models\Inquiry::class);
    }
}
