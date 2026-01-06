<?php

namespace App\Models\Config;

use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    protected $table = 'system_settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = true;

    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'boolean',
    ];

    /**
     * Verifica se o sistema está em manutenção
     */
    public static function isActive(): bool
    {
        return self::where('key', 'maintenance_mode')
            ->value('value') ?? false;
    }

    /**
     * Mensagem padrão de manutenção
     */
    public static function getMessage(): string
    {
        return 'Sistema em manutenção, tente novamente mais tarde';
    }

    /**
     * Atualiza status de manutenção
     */
    public static function updateStatus(bool $active): void
    {
        self::updateOrCreate(
            ['key' => 'maintenance_mode'],
            ['value' => $active]
        );
    }
}