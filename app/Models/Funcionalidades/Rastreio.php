<?php

namespace App\Models\Funcionalidades;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Rastreio extends BaseModel
{
    use HasFactory;

    protected $table = 'rastreio'; // Nome explícito da tabela

    protected $fillable = [
        'dt_id',
        'motorista',
        'cpf',
        'placa_cavalo',
        'placa_reboque',
        'tipo_rastreador',
        'status_rastreador',
        'buonny',
        'brk',
        'check_list',
        'id_rastreador',
        'login_rast',
        'pass_rast',
        'status',
        'user_id',
    ];
}
