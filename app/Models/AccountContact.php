<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountContact extends Pivot
{
    protected $table = 'account_contact';

    protected $fillable = [
        'account_id',
        'contact_id',
        'company_name',
        'relationship_type',
    ];
}