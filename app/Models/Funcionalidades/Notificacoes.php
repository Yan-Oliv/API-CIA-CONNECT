<?php

namespace App\Models\Funcionalidades;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacoes extends Model
{
    use HasFactory;

    protected $table = 'notificacoes';

    protected $fillable = [
        'message',
        'type',
        'read',
        'created_by',
        'visible_to_roles',
        'visible_to_users',
        'visible_to_filial',
        'context',
    ];


    // Desativa os timestamps automáticos do Laravel
    public $timestamps = false;

    // Casts automáticos
    protected $casts = [
        'visible_to_roles'  => 'array',
        'visible_to_users'  => 'array',
        'visible_to_filial' => 'array',   // <-- ADICIONE AQUI
        'read'              => 'boolean',
        'timestamp'         => 'datetime',
    ];
}
