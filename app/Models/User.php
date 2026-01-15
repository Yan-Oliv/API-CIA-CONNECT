<?php

namespace App\Models;

use App\Models\Referencias\Filial;
use App\Models\Referencias\Cliente;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    // timestamps customizados
    public const CREATED_AT = null;
    public const UPDATED_AT = 'last_update';

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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'first_login' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONAMENTOS
    |--------------------------------------------------------------------------
    */

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'filial_id');
    }

    public function clientes()
    {
        return $this->belongsToMany(
            Cliente::class,
            'cliente_gestor',
            'user_id',
            'cliente_id'
        );
    }
}
