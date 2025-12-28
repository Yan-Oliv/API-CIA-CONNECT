<?php

namespace App\Models\Referencias;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotoristaUfs extends Model
{
    use HasFactory;

    // Desabilita a tabela de timestamps
    public $timestamps = false;

    // Definir os campos que podem ser preenchidos
    protected $fillable = [
        'motorista_id',
        'uf_id'
    ];

    // Relacionamento com o modelo Motorista
    public function motorista()
    {
        return $this->belongsTo(Motoristas::class, 'motorista_id');
    }

    // Relacionamento com a tabela 'ufs' via a tabela intermediária
    public function ufs()
    {
        return $this->belongsToMany(Estado::class, 'motoristas_ufs', 'motorista_id', 'uf_id');
    }

}
