<?php

namespace App\Models\Utilities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Contatos extends BaseModel
{
    use HasFactory;

    protected $table = 'contatos';

    protected $fillable = [
        'title',
        'desc',
        'numero',
        'user_id',
    ];

}
