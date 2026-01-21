<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - ADICIONA APENAS COLUNAS QUE NÃO EXISTEM
     */
    public function up(): void
    {
        // 1. Primeiro, verifique se a tabela existe
        if (!Schema::hasTable('personal_access_tokens')) {
            // Se não existir, crie (improvável, mas por segurança)
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
                
                // Novas colunas para múltiplas sessões
                $table->string('device_id')->nullable()->after('tokenable_id');
                $table->string('device_name')->nullable()->after('device_id');
                $table->string('ip_address')->nullable()->after('device_name');
            });
            return;
        }

        // 2. Se a tabela já existe, adicione apenas as colunas que faltam
        
        // Adiciona device_id se não existir
        if (!Schema::hasColumn('personal_access_tokens', 'device_id')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->string('device_id')->nullable()->after('tokenable_id');
            });
        }

        // Adiciona device_name se não existir
        if (!Schema::hasColumn('personal_access_tokens', 'device_name')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->string('device_name')->nullable()->after('device_id');
            });
        }

        // Adiciona ip_address se não existir
        if (!Schema::hasColumn('personal_access_tokens', 'ip_address')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->string('ip_address')->nullable()->after('device_name');
            });
        }

        // expires_at já deve existir do Sanctum, mas verifica
        if (!Schema::hasColumn('personal_access_tokens', 'expires_at')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->timestamp('expires_at')->nullable()->after('last_used_at');
            });
        }

        // 3. Adiciona índices para performance
        $this->addIndexesSafely();
    }

    /**
     * Reverse the migrations - REMOVE APENAS COLUNAS QUE ADICIONAMOS
     */
    public function down(): void
    {
        if (!Schema::hasTable('personal_access_tokens')) {
            return;
        }

        // Remove apenas as colunas que esta migration adicionou
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Lista de colunas que esta migration pode ter adicionado
            $columnsToRemove = ['device_id', 'device_name', 'ip_address'];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('personal_access_tokens', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            // NÃO remove expires_at pois é coluna padrão do Sanctum
            // NÃO remove last_used_at pois é coluna padrão do Sanctum
            
            // Remove índices que esta migration pode ter adicionado
            $this->removeIndexesSafely();
        });
    }

    /**
     * Adiciona índices de forma segura (apenas se não existirem)
     */
    private function addIndexesSafely(): void
    {
        // Índice para device_id
        if (!Schema::hasIndex('personal_access_tokens', 'personal_access_tokens_device_id_index')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->index('device_id', 'personal_access_tokens_device_id_index');
            });
        }

        // Índice para expires_at
        if (!Schema::hasIndex('personal_access_tokens', 'personal_access_tokens_expires_at_index')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->index('expires_at', 'personal_access_tokens_expires_at_index');
            });
        }

        // Índice composto para queries frequentes
        $compositeIndexName = 'personal_access_tokens_tokenable_expires_index';
        if (!Schema::hasIndex('personal_access_tokens', $compositeIndexName)) {
            Schema::table('personal_access_tokens', function (Blueprint $table) use ($compositeIndexName) {
                $table->index(['tokenable_type', 'tokenable_id', 'expires_at'], $compositeIndexName);
            });
        }
    }

    /**
     * Remove índices de forma segura
     */
    private function removeIndexesSafely(): void
    {
        // Lista de índices que esta migration pode ter adicionado
        $indexesToRemove = [
            'personal_access_tokens_device_id_index',
            'personal_access_tokens_expires_at_index',
            'personal_access_tokens_tokenable_expires_index',
        ];

        foreach ($indexesToRemove as $indexName) {
            if (Schema::hasIndex('personal_access_tokens', $indexName)) {
                Schema::table('personal_access_tokens', function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            }
        }
    }
};