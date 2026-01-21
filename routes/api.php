<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Controllers – Core / Auth
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Controllers – Config
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Config\MaintenanceController;

/*
|--------------------------------------------------------------------------
| Controllers – Backup
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\BackupController;

/*
|--------------------------------------------------------------------------
| Controllers – Funcionalidades
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Funcionalidades\{
    UsersController,
    ConsultasController,
    ManifestoController,
    AcompanhamentoController,
    LembreteController,
    MotoristasController,
    CargasController,
    MensagensController,
    RastreioController,
    NotificationController
};

/*
|--------------------------------------------------------------------------
| Controllers – Referências
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Referencias\{
    EstadosController,
    RequisitosController,
    CarroceriaController,
    VeiculosController,
    ClientesController,
    FilialController,
    CdClientesController
};

/*
|--------------------------------------------------------------------------
| Controllers – Utilidades
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Utilities\{
    ContatosController,
    LinksController
};

/*
|--------------------------------------------------------------------------
| HEALTH / DEBUG
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => response()->json(['status' => 'OK'], 200));

// Rota de teste SIMPLES - sem middleware
Route::get('/test', function() {
    return response()->json([
        'status' => 'ok',
        'message' => 'API está funcionando',
        'timestamp' => now(),
        'php_version' => phpversion(),
        'laravel_version' => app()->version(),
    ]);
});

// Rota de teste de DB - sem autenticação
Route::get('/test-db', function() {
    try {
        // Teste conexão PDO
        $pdo = DB::connection()->getPdo();
        $database = DB::connection()->getDatabaseName();
        
        // Teste query simples
        $result = DB::select('SELECT version() as version');
        
        return response()->json([
            'db_status' => 'connected',
            'database' => $database,
            'pgsql_version' => $result[0]->version ?? 'unknown',
            'env' => [
                'db_host' => env('DB_HOST'),
                'db_port' => env('DB_PORT'),
                'db_database' => env('DB_DATABASE'),
                'db_username' => env('DB_USERNAME'),
                'db_sslmode' => env('DB_SSLMODE'),
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'db_status' => 'error',
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'env_debug' => [
                'db_host' => env('DB_HOST'),
                'db_port' => env('DB_PORT'),
                'db_database' => env('DB_DATABASE'),
                'db_username' => env('DB_USERNAME'),
                'app_debug' => env('APP_DEBUG'),
            ],
        ], 500);
    }
});

Route::get('/debug-db', function() {
    try {
        // Teste conexão básica
        $host = env('DB_HOST');
        $port = env('DB_PORT');
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        
        return response()->json([
            'config' => [
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'username' => $username,
                'connection' => env('DB_CONNECTION'),
                'sslmode' => env('DB_SSLMODE'),
            ],
            'env_loaded' => app()->environment(),
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Teste de rede DO CONTAINER Railway
Route::get('/container-network-test', function () {
    $results = [];
    $host = 'db.fonrobpijqhhodsgxflz.supabase.co';
    
    // Função para testar portas
    function testPort($host, $port, $timeout = 5) {
        $start = microtime(true);
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);
        
        $fp = @stream_socket_client(
            "tcp://{$host}:{$port}",
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );
        
        $responseTime = microtime(true) - $start;
        
        if ($fp) {
            fclose($fp);
            return [
                'success' => true,
                'error' => null,
                'response_time' => round($responseTime * 1000, 2) . 'ms'
            ];
        }
        
        return [
            'success' => false,
            'error' => $errstr,
            'error_no' => $errno,
            'response_time' => round($responseTime * 1000, 2) . 'ms'
        ];
    }
    
    // Testar portas
    $results['port_5432'] = testPort($host, 5432);
    $results['port_6543'] = testPort($host, 6543);
    
    // Testar DNS
    $dnsStart = microtime(true);
    $ips = @dns_get_record($host, DNS_A);
    $dnsTime = microtime(true) - $dnsStart;
    
    $results['dns'] = [
        'host' => $host,
        'ips' => $ips ?: [],
        'success' => !empty($ips),
        'response_time' => round($dnsTime * 1000, 2) . 'ms',
        'gethostbyname' => @gethostbyname($host),
    ];
    
    // Informações do container
    $results['container_info'] = [
        'php_uname' => php_uname(),
        'php_sapi' => php_sapi_name(),
        'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
        'remote_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'current_time' => now()->toISOString(),
    ];
    
    // Testar conexão PDO direta
    try {
        $dsn = "pgsql:host={$host};port=6543;dbname=postgres;sslmode=require";
        $start = microtime(true);
        $pdo = new PDO(
            $dsn,
            'postgres',
            'Test01001*11112002',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]
        );
        $pdoTime = microtime(true) - $start;
        
        $stmt = $pdo->query('SELECT 1 as test, current_database() as db');
        $dbResult = $stmt->fetch();
        
        $results['pdo_connection'] = [
            'success' => true,
            'response_time' => round($pdoTime * 1000, 2) . 'ms',
            'query_result' => $dbResult,
            'pdo_drivers' => PDO::getAvailableDrivers(),
        ];
    } catch (PDOException $e) {
        $results['pdo_connection'] = [
            'success' => false,
            'error' => $e->getMessage(),
            'error_code' => $e->getCode(),
        ];
    }
    
    return response()->json($results);
});

// Teste de conexão via Laravel DB facade
Route::get('/test-db-connection', function () {
    $config = config('database.connections.pgsql');
    
    // Remover senha do log por segurança
    $logConfig = $config;
    unset($logConfig['password']);
    
    try {
        // Tentar conexão direta
        DB::connection()->getPdo();
        
        // Testar query
        $result = DB::select('SELECT version() as version, current_database() as db, current_user as user');
        
        return response()->json([
            'status' => 'success',
            'message' => 'Conexão com banco de dados estabelecida',
            'config' => $logConfig,
            'database_info' => $result[0] ?? null,
            'timestamp' => now()->toISOString(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Falha na conexão com o banco de dados',
            'error' => $e->getMessage(),
            'error_class' => get_class($e),
            'config' => $logConfig,
            'php_pdo_drivers' => PDO::getAvailableDrivers(),
            'laravel_env' => app()->environment(),
            'timestamp' => now()->toISOString(),
        ], 500);
    }
});

// Teste de configuração atual
Route::get('/current-config', function () {
    $config = config('database.connections.pgsql');
    unset($config['password']); // Segurança
    
    return response()->json([
        'database_config' => $config,
        'env_variables' => [
            'DB_HOST' => env('DB_HOST'),
            'DB_PORT' => env('DB_PORT'),
            'DB_DATABASE' => env('DB_DATABASE'),
            'DB_USERNAME' => env('DB_USERNAME'),
            'DB_SSLMODE' => env('DB_SSLMODE'),
            'APP_ENV' => env('APP_ENV'),
        ],
        'railway_vars' => [
            'RAILWAY_ENVIRONMENT' => env('RAILWAY_ENVIRONMENT'),
            'RAILWAY_SERVICE_NAME' => env('RAILWAY_SERVICE_NAME'),
        ],
    ]);
});

Route::get('/test-connection', function() {
    $host = env('DB_HOST');
    $port = env('DB_PORT');
    
    $timeout = 5;
    $connected = false;
    
    try {
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if ($socket) {
            fclose($socket);
            $connected = true;
        }
    } catch (\Exception $e) {
        $connected = false;
    }
    
    return response()->json([
        'host' => $host,
        'port' => $port,
        'connection_test' => $connected ? 'SUCCESS' : 'FAILED',
        'error' => $errstr ?? null,
        'error_no' => $errno ?? null,
    ]);
});

Route::get('/user', fn (Request $request) => $request->user());

Route::get('/debug-base-model', fn () =>
    class_exists(\App\Models\BaseModel::class)
        ? 'BaseModel OK'
        : 'BaseModel NOT FOUND'
);

Route::options('/{any}', function () {
    return response()->noContent();
})->where('any', '.*');

/*
|--------------------------------------------------------------------------
| CONFIGURAÇÕES GLOBAIS (PUBLIC)
|--------------------------------------------------------------------------
*/
Route::prefix('config')->group(function () {
    // Status público de manutenção (Flutter consome aqui)
    Route::get('/maintenance', [MaintenanceController::class, 'status']);
});

/*
|--------------------------------------------------------------------------
| CONFIGURAÇÕES GLOBAIS (PROTECTED – ADM)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->prefix('config')->group(function () {
    Route::post('/maintenance', [MaintenanceController::class, 'update']);
});

/*
|--------------------------------------------------------------------------
| AUTENTICAÇÃO (PUBLIC)
|--------------------------------------------------------------------------
*/
// Rota de login - PÚBLICA
Route::match(['get', 'post'], '/login', [AuthController::class, 'login'])->name('login');
Route::put('/users/{id}/rec', [UsersController::class, 'resetPasswordByEmail']);

/*
|--------------------------------------------------------------------------
| AUTENTICAÇÃO (PROTECTED)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->middleware('auth:sanctum')->group(function () {
    // Validação de token (mantida para compatibilidade)
    Route::get('/validate', [AuthController::class, 'validateToken']);
    
    // Logout por dispositivo
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Listar sessões ativas do usuário
    Route::get('/sessions', [AuthController::class, 'listSessions']);
    
    // Revogar sessão específica
    Route::delete('/sessions/{deviceId}', [AuthController::class, 'revokeSession']);
});

Route::get('/validate', [AuthController::class, 'validateToken'])
    ->middleware('auth:sanctum')
    ->name('validate.legacy');

/*
|--------------------------------------------------------------------------
| BACKUP (PROTECTED)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')
    ->prefix('backup')
    ->group(function () {
        Route::post('/enviar', [BackupController::class, 'enviarBackup']);
    });

/*
|--------------------------------------------------------------------------
| API PROTECTED — CONFIRM / INDEX
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Users
    Route::get('/users', [UsersController::class, 'index']);
    Route::post('/user/{email}', [UsersController::class, 'listEmail']);

    // Funcionalidades
    Route::get('/consultas', [ConsultasController::class, 'index']);
    Route::get('/manifesto', [ManifestoController::class, 'index']);
    Route::get('/acompanhamento', [AcompanhamentoController::class, 'index']);
    Route::get('/lembrete', [LembreteController::class, 'index']);
    Route::get('/motoristas', [MotoristasController::class, 'index']);
    Route::get('/cargas', [CargasController::class, 'index']);
    Route::get('/mensagens', [MensagensController::class, 'index']);
    Route::get('/rastreio', [RastreioController::class, 'index']);

    // Notificações
    Route::get('/notificacoes', [NotificationController::class, 'index']);

    // Referências
    Route::get('/estados', [EstadosController::class, 'index']);
    Route::get('/req', [RequisitosController::class, 'index']);
    Route::get('/car', [CarroceriaController::class, 'index']);
    Route::get('/vei', [VeiculosController::class, 'index']);
    Route::get('/clis', [ClientesController::class, 'index']);
    Route::get('/fli', [FilialController::class, 'index']);
    Route::get('/cds', [CdClientesController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| API PROTECTED — FUNCIONALIDADES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('users')->group(function () {
        Route::get('/s', [UsersController::class, 'search']);
        Route::get('/{id}', [UsersController::class, 'filter']);
        Route::post('/', [UsersController::class, 'cad']);
        Route::put('/{id}', [UsersController::class, 'edit']);
        Route::put('/{id}/change', [UsersController::class, 'changePassword']);
        Route::delete('/{id}', [UsersController::class, 'delete']);
    });

    Route::prefix('consultas')->group(function () {
        Route::get('/s', [ConsultasController::class, 'search']);
        Route::get('/{id}', [ConsultasController::class, 'filter']);
        Route::post('/', [ConsultasController::class, 'cad']);
        Route::put('/{id}', [ConsultasController::class, 'edit']);
        Route::delete('/{id}', [ConsultasController::class, 'delete']);
        Route::delete('/del', [ConsultasController::class, 'deleteEnviados']);
        Route::delete('/clear', [ConsultasController::class, 'destroy']);
    });

    Route::prefix('manifesto')->group(function () {
        Route::get('/s', [ManifestoController::class, 'search']);
        Route::get('/{id}', [ManifestoController::class, 'filter']);
        Route::post('/', [ManifestoController::class, 'cad']);
        Route::put('/{id}', [ManifestoController::class, 'edit']);
        Route::delete('/{id}', [ManifestoController::class, 'delete']);
    });

    Route::prefix('acompanhamento')->group(function () {
        Route::get('/s', [AcompanhamentoController::class, 'search']);
        Route::get('/{id}', [AcompanhamentoController::class, 'filter']);
        Route::post('/', [AcompanhamentoController::class, 'cad']);
        Route::put('/{id}', [AcompanhamentoController::class, 'edit']);
        Route::delete('/{id}', [AcompanhamentoController::class, 'delete']);
    });

    Route::prefix('lembrete')->group(function () {
        Route::post('/s', [LembreteController::class, 'search']);
        Route::get('/{id}', [LembreteController::class, 'filter']);
        Route::post('/', [LembreteController::class, 'cad']);
        Route::put('/{id}', [LembreteController::class, 'edit']);
        Route::patch('/{id}/done', [LembreteController::class, 'done']);
        Route::delete('/{id}', [LembreteController::class, 'delete']);
    });

    Route::prefix('motoristas')->group(function () {
        Route::get('/s', [MotoristasController::class, 'search']);
        Route::get('/{id}', [MotoristasController::class, 'filter']);
        Route::post('/', [MotoristasController::class, 'cad']);
        Route::put('/{id}', [MotoristasController::class, 'edit']);
        Route::delete('/{id}', [MotoristasController::class, 'delete']);
    });

    Route::prefix('cargas')->group(function () {
        Route::get('/s', [CargasController::class, 'search']);
        Route::get('/{id}', [CargasController::class, 'filter']);
        Route::post('/', [CargasController::class, 'cad']);
        Route::put('/{id}', [CargasController::class, 'edit']);
        Route::delete('/{id}', [CargasController::class, 'delete']);
    });

    Route::prefix('mensagens')->group(function () {
        Route::get('/s', [MensagensController::class, 'search']);
        Route::post('/', [MensagensController::class, 'cad']);
        Route::delete('/{id}', [MensagensController::class, 'delete']);
    });

    Route::prefix('rastreio')->group(function () {
        Route::get('/s', [RastreioController::class, 'search']);
        Route::get('/{id}', [RastreioController::class, 'filter']);
        Route::post('/', [RastreioController::class, 'cad']);
        Route::put('/{id}', [RastreioController::class, 'edit']);
        Route::delete('/{id}', [RastreioController::class, 'delete']);
    });

    Route::prefix('notificacoes')->group(function () {
        Route::post('/search', [NotificationController::class, 'search']);
        Route::post('/', [NotificationController::class, 'store']);
        Route::post('/lidas', [NotificationController::class, 'markAllRead']);
    });
});

/*
|--------------------------------------------------------------------------
| API PROTECTED — REFERÊNCIAS & UTILIDADES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('estados')->group(function () {
        Route::get('/s', [EstadosController::class, 'search']);
        Route::get('/{id}', [EstadosController::class, 'filter']);
        Route::post('/filter', [EstadosController::class, 'filterEstados']);
        Route::post('/', [EstadosController::class, 'cad']);
        Route::put('/{id}', [EstadosController::class, 'edit']);
        Route::delete('/{id}', [EstadosController::class, 'delete']);
    });

    Route::prefix('req')->group(function () {
        Route::get('/s', [RequisitosController::class, 'search']);
        Route::get('/{id}', [RequisitosController::class, 'filter']);
        Route::post('/filter', [RequisitosController::class, 'filterRequisitos']);
        Route::post('/', [RequisitosController::class, 'cad']);
        Route::put('/{id}', [RequisitosController::class, 'edit']);
        Route::delete('/{id}', [RequisitosController::class, 'delete']);
    });

    Route::prefix('car')->group(function () {
        Route::get('/s', [CarroceriaController::class, 'search']);
        Route::get('/{id}', [CarroceriaController::class, 'filter']);
        Route::post('/filter', [CarroceriaController::class, 'filterCarrocerias']);
        Route::post('/', [CarroceriaController::class, 'cad']);
        Route::put('/{id}', [CarroceriaController::class, 'edit']);
        Route::delete('/{id}', [CarroceriaController::class, 'delete']);
    });

    Route::prefix('vei')->group(function () {
        Route::get('/s', [VeiculosController::class, 'search']);
        Route::get('/{id}', [VeiculosController::class, 'filter']);
        Route::post('/filter', [VeiculosController::class, 'filterVeiculos']);
        Route::post('/', [VeiculosController::class, 'cad']);
        Route::put('/{id}', [VeiculosController::class, 'edit']);
        Route::delete('/{id}', [VeiculosController::class, 'delete']);
    });

    Route::prefix('clis')->group(function () {
        Route::get('/s', [ClientesController::class, 'search']);
        Route::get('/{id}', [ClientesController::class, 'filter']);
        Route::post('/filter', [ClientesController::class, 'filterClientes']);
        Route::post('/', [ClientesController::class, 'cad']);
        Route::put('/{id}', [ClientesController::class, 'edit']);
        Route::delete('/{id}', [ClientesController::class, 'delete']);
    });

    Route::prefix('fli')->group(function () {
        Route::get('/s', [FilialController::class, 'search']);
        Route::get('/{id}', [FilialController::class, 'filter']);
        Route::post('/filter', [FilialController::class, 'filterFilial']);
        Route::post('/', [FilialController::class, 'cad']);
        Route::put('/{id}', [FilialController::class, 'edit']);
        Route::delete('/{id}', [FilialController::class, 'delete']);
    });

    Route::prefix('cds')->group(function () {
        Route::get('/s', [CdClientesController::class, 'search']);
        Route::get('/{id}', [CdClientesController::class, 'filter']);
        Route::post('/filter', [CdClientesController::class, 'filterCds']);
        Route::post('/', [CdClientesController::class, 'cad']);
        Route::put('/{id}', [CdClientesController::class, 'edit']);
        Route::delete('/{id}', [CdClientesController::class, 'delete']);
    });

    Route::prefix('contatos')->group(function () {
        Route::get('/s', [ContatosController::class, 'search']);
        Route::get('/{id}', [ContatosController::class, 'filter']);
        Route::post('/', [ContatosController::class, 'cad']);
        Route::put('/{id}', [ContatosController::class, 'edit']);
        Route::delete('/{id}', [ContatosController::class, 'delete']);
        Route::delete('/clear', [ContatosController::class, 'destroy']);
    });

    Route::prefix('links')->group(function () {
        Route::get('/s', [LinksController::class, 'search']);
        Route::get('/{id}', [LinksController::class, 'filter']);
        Route::post('/', [LinksController::class, 'cad']);
        Route::put('/{id}', [LinksController::class, 'edit']);
        Route::delete('/{id}', [LinksController::class, 'delete']);
        Route::delete('/clear', [LinksController::class, 'destroy']);
    });
});

/*
|---------------------------------------------------------------------
|		404 ROUTE
|---------------------------------------------------------------------
*/

Route::any('/{any}', function () {
    return response()->json(['message' => 'Not found'], 404);
})->where('any', '.*');

