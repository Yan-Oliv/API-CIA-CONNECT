<?php

namespace App\Models\Funcionalidades;

use App\Models\Funcionalidades\Users;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Lembrete extends BaseModel
{
    use HasFactory;

    protected $table = 'lembretes';

    protected $fillable = [
        'titulo',
        'lembrete',
        'cor',
        'user_id',
        'feito',
    ];

    /**
     * Criador do lembrete
     */
    public function criador()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    protected $casts = [
        'feito' => 'boolean',
    ];


    /**
     * Usuários que podem visualizar o lembrete (relacionamento N:N)
     */
    public function visivelPara()
    {
        return $this->belongsToMany(Users::class, 'lembretes_usuarios', 'lembrete_id', 'user_id');
    }

}
