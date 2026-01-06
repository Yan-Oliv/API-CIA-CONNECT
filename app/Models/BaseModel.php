<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    public const CREATED_AT = null;
    public const UPDATED_AT = 'last_update';
}
