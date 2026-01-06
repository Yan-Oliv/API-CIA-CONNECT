<?php

namespace App\Models\Funcionalidades;

use App\Models\Referencias\Filial;
use App\Models\Referencias\Cliente;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Users extends BaseModel
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'perfil',
        'name',
        'email',
        'telefone',
        'password',
        'role',
        'filial_id',
        'first_login',
    ];

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'filial_id');
    }
    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'cliente_gestor', 'user_id', 'cliente_id');
    }
}
