<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verifica e adiciona apenas colunas que não existem
        if (!Schema::hasColumn('personal_access_tokens', 'device_id')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->string('device_id')->nullable()->after('tokenable_id');
            });
        }

        if (!Schema::hasColumn('personal_access_tokens', 'device_name')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->string('device_name')->nullable()->after('device_id');
            });
        }

        if (!Schema::hasColumn('personal_access_tokens', 'ip_address')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->string('ip_address')->nullable()->after('device_name');
            });
        }

        if (!Schema::hasColumn('personal_access_tokens', 'last_used_at')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->timestamp('last_used_at')->nullable()->after('abilities');
            });
        }

        if (!Schema::hasColumn('personal_access_tokens', 'expires_at')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->timestamp('expires_at')->nullable()->after('last_used_at');
            });
        }

        // Adiciona índices se não existirem
        if (!Schema::hasIndex('personal_access_tokens', 'personal_access_tokens_device_id_index')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->index('device_id');
            });
        }

        if (!Schema::hasIndex('personal_access_tokens', 'personal_access_tokens_last_used_at_index')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->index('last_used_at');
            });
        }

        if (!Schema::hasIndex('personal_access_tokens', 'personal_access_tokens_expires_at_index')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->index('expires_at');
            });
        }
    }

    public function down(): void
    {
        // Remove apenas as colunas que foram adicionadas por esta migration
        // NÃO remova colunas que já existiam antes
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            if (Schema::hasColumn('personal_access_tokens', 'device_id')) {
                $table->dropColumn('device_id');
            }
            
            if (Schema::hasColumn('personal_access_tokens', 'device_name')) {
                $table->dropColumn('device_name');
            }
            
            if (Schema::hasColumn('personal_access_tokens', 'ip_address')) {
                $table->dropColumn('ip_address');
            }
            
            // Não remova last_used_at pois já existia
            // Não remova expires_at se já existia
            
            // Remove índices
            $table->dropIndexIfExists('personal_access_tokens_device_id_index');
            $table->dropIndexIfExists('personal_access_tokens_last_used_at_index');
            $table->dropIndexIfExists('personal_access_tokens_expires_at_index');
        });
    }
};