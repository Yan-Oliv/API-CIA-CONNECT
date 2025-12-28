<?php

namespace App\Models\Referencias;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Carroceria extends BaseModel
{
    use HasFactory;

    // Define o nome da tabela
    protected $table = 'carrocerias';

    protected $fillable = [
        'nome',
        'tipo',
        'user_id',
    ];
    
}