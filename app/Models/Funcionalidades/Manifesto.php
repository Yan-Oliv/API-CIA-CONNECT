<?php

namespace App\Models\Funcionalidades;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Manifesto extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'ciot',
        'cliente_id',
        'cliente_backup',
        'filial',
        'destino',
        'motorista',
        'tipo_veiculo',
        'placa_cavalo',
        'placa_carreta',
        'observacao',
        'valor',
        'data_entrega',
        'porcentagem',
        'entrega',
        'rota',
        'tag',
        'antt',
        'doc_antt',
        'tributos',
        'desconto',
        'responsavel',
        'banco',
        'agencia',
        'conta',
        'tipo_pix',
        'chave_pix',
        'favorecido',
        'user_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
