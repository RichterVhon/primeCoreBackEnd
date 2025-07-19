<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ContactListing extends Pivot
{
    use SoftDeletes;
    protected $table = 'contact_listing'; // or whatever your pivot table is
    protected $dates = ['deleted_at'];
}

