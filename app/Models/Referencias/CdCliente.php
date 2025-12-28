<?php

namespace App\Models\Referencias;

use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;
use App\Models\Referencias\Cliente;


class CdCliente extends BaseModel
{
    protected $table = 'cd_clientes';

    protected $fillable = [
        'cliente_id',
        'nome_filial',
        'cidade',
        'estado',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
