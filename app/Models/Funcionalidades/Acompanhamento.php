<?php

namespace App\Models\Funcionalidades;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Acompanhamento extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'cte',
        'cliente_id',
        'produto',
        'motorista',
        'contato_motorista',
        'telefone_motorista',
        'valor_negociado',
        'origem',
        'destino',
        'dia_carregamento',
        'agenda_descarga',
        'data_chegada',
        'hora_chegada',
        'nome_patrao',
        'telefone_patrao',
        'veiculo_id',
        'status',
        'user_id',
    ];

}