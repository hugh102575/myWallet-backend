<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddedTokens extends Model
{
    protected $table = 'user_added_tokens';

    public $timestamps = false;

    protected $guarded = [];
}
