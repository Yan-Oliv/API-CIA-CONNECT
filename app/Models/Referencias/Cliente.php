<?php

namespace App\Models\Referencias;

use App\Models\Funcionalidades\Users;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;
use App\Models\Referencias\CdCliente;


class Cliente extends BaseModel
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'nome',
        'user_id',
    ];

    public function cds()
    {
        return $this->hasMany(CdCliente::class, 'cliente_id');
    }

    public function gestores()
    {
        return $this->belongsToMany(Users::class, 'cliente_gestor', 'cliente_id', 'user_id');
    }

}
