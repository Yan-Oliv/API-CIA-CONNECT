<?php

namespace App\Models\Utilities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Links extends BaseModel
{
    use HasFactory;

    protected $table = 'links';

    protected $fillable = [
        'title',
        'desc',
        'link',
        'user_id',
    ];
}
