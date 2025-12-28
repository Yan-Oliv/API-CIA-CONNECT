<?php

namespace App\Models\Referencias;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;

    // Define o nome da tabela
    protected $table = 'ufs';

    protected $fillable = [
        'state_name',
        'state_sigla',
        'user_id',
    ];
    // Desativa o gerenciamento padrão de timestamps automáticos
    const CREATED_AT = null; // Sem coluna de criação
    const UPDATED_AT = 'last_update'; // Substitui updated_at
}