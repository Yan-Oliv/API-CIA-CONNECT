<?php

namespace App\Models\Referencias;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Filial extends BaseModel
{
    use HasFactory;

    // Define o nome da tabela
    protected $table = 'filiais';

    protected $fillable = [
        'filial',
        'estado',
        'user_id',
    ];
}