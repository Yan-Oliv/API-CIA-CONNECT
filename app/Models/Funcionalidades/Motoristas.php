<?php

namespace App\Models\Funcionalidades;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Referencias\Veiculo;
use App\Models\Referencias\Estado;
use App\Models\BaseModel;

class Motoristas extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'telefone',
        'vei_id',
        'car_id', 
        'quantidade_paletes',
        'peso',
        'metragem_cubica',
        'user_id',
        'placa_cavalo',
        'placa_reboque',
        'placa_segundo',
        'placa_terceiro',
        'antt',
        'doc_cavalo',
        'cpf',
        'banco',
        'agencia',
        'conta',
        'pix',
        'tipo_pix',
        'beneficiario',
        'telefone_patrao',
        'tag',
        'eixos',
        'mopp', 
        'rastreador',   
        'status',    
        'observacao'
    ];

    // Relacionamento com a tabela 'veiculos'
    public function veiculo() {
        return $this->belongsTo('App\Models\Referencias\Veiculo', 'vei_id');
    }

    // Relacionamento com a tabela 'users'
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    // Relacionamento com a tabela 'ufs' via a tabela intermediária
    public function ufs()
    {
        return $this->belongsToMany(Estado::class, 'motorista_ufs', 'motorista_id', 'uf_id');
    }

    // Relacionamento com a tabela 'carrocerias'
    public function carroceria()
    {
        return $this->belongsTo('App\Models\Referencias\Carroceria', 'car_id');
    }
}
