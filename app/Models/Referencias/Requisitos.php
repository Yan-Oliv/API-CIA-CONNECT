<?php

namespace App\Models\Referencias;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Requisitos extends BaseModel
{
    use HasFactory;

    // Define o nome da tabela
    protected $table = 'requisitos';

    protected $fillable = [
        'requisitos',
        'user_id',
    ];

}