<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupHasUser extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'group_id',
        'user_id',
    ];
}
