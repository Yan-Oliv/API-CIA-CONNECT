<?php

namespace App\Models\Funcionalidades;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Consultas extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'motorista',
        'buony',
        'consulta',
        'destino',
        'cliente_id',
        'user_id',
        'status',
        "observacao",
    ];
    
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}