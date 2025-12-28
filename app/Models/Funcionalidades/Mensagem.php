<?php

namespace App\Models\Funcionalidades;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Referencias\Cliente;
use App\Models\BaseModel;

class Mensagem extends BaseModel
{
    use HasFactory;

    protected $table = 'mensagens';

    protected $fillable = [
        'cliente_id',
        'title',
        'texto',
        'imagem',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
