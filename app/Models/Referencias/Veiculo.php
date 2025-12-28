<?php

namespace App\Models\Referencias;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;
use App\Models\Funcionalidades\Motoristas;


class Veiculo extends BaseModel
{
    use HasFactory;

    // Define o nome da tabela
    protected $table = 'veiculos';

    protected $fillable = [
        'nome',
        'tipo',
        'user_id',
    ];

    // Relacionamento com motoristas
    public function motoristas()
    {
        return $this->hasMany(Motoristas::class, 'vei_id');
    }
}