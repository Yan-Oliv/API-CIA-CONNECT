<?php

namespace App\Models\Funcionalidades;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Cargas extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'cod_carga',
        'titulo',
        'produto',
        'cliente_id',
        'cliente_backup',
        'tipo_carregamento',
        'tamanho_veiculo',
        'status',
        'cidade_origem',
        'cidade_destino',
        'uf_id',
        'carregamento',
        'descarga',
        'peso',
        'valor',
        'adiantamento',
        'observacao',
        'filial_id',
        'user_id',
    ];

    // Relacionamentos
    public function uf()
    {
        return $this->belongsTo(Estado::class, 'uf_id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    public function requisitos()
    {
        return $this->belongsToMany(Requisitos::class, 'carga_requisitos', 'carga_id', 'req_id');
    }

    public function carrocerias()
    {
        return $this->belongsToMany(Carroceria::class, 'carga_carrocerias', 'carga_id', 'car_id');
    }

    public function veiculos()
    {
        return $this->belongsToMany(Veiculos::class, 'carga_veiculos', 'carga_id', 'veic_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'filial_id');
    }

}
