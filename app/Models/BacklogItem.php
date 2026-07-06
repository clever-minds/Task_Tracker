<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BacklogItem extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
    ];
}
