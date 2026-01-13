<?php

namespace App\Domain\Consultas;

class ConsultaMapper
{
    /**
     * Retorna o nome da Gerenciadora de Risco (GR),
     * mantendo compatibilidade com o legado (buony).
     */
    public static function gr(object $consulta): string
    {
        return $consulta->buony ?? 'Não informado';
    }
}

