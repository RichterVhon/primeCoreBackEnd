<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountContact extends Pivot
{
    use SoftDeletes;
    protected $table = 'account_contact';

    protected $fillable = [
        'account_id',
        'contact_id',
        'company_name',
        'relationship_type',
    ];
}