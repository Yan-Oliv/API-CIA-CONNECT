<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Biblioteca Backup
use App\Http\Controllers\BackupController;

// Bibliotecas Funcionalidades
use App\Http\Controllers\Funcionalidades\ConsultasController;
use App\Http\Controllers\Funcionalidades\UsersController;
use App\Http\Controllers\Funcionalidades\ManifestoController;
use App\Http\Controllers\Funcionalidades\AcompanhamentoController;
use App\Http\Controllers\Funcionalidades\LembreteController;
use App\Http\Controllers\Funcionalidades\MotoristasController;
use App\Http\Controllers\Funcionalidades\CargasController;
use App\Http\Controllers\Funcionalidades\MensagensController;
use App\Http\Controllers\Funcionalidades\RastreioController;
use App\Http\Controllers\Funcionalidades\NotificationController;

// Bibliotecas Referencias
use App\Http\Controllers\Referencias\EstadosController;
use App\Http\Controllers\Referencias\RequisitosController;
use App\Http\Controllers\Referencias\CarroceriaController;
use App\Http\Controllers\Referencias\VeiculosController;
use App\Http\Controllers\Referencias\ClientesController;
use App\Http\Controllers\Referencias\FilialController;
use App\Http\Controllers\Referencias\CdClientesController;

//Bibliotecas Utilidades
use App\Http\Controllers\Utilities\ContatosController;
use App\Http\Controllers\Utilities\LinksController;


Route::get('/user', function (Request $request) {
    return $request->user();
});

//Rota para teste da API.
Route::get('/', function () {
    return response()->json(['status' => 'OK'], 200);
});

// ##### ROTAS DE AUTENTICAÇÃO E LOGIN #####

Route::post('/log', [UsersController::class, 'login']);

Route::match(['get', 'post'], '/login', [AuthController::class, 'login'])->name('login');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/validate', [AuthController::class, 'validateToken'])->middleware('auth:sanctum');

// ##### ROTAS DE BACKUP #####

Route::post('/backup/enviar', [BackupController::class, 'enviarBackup']);

// ##### ROTAS DE FUNCIONALIDADE #####

// ##### CONFIRM ROUTES #####

// Users confirm routes
Route::get('/users', [UsersController::class, 'index']);
Route::put('/users/{id}/rec', [UsersController::class, 'resetPasswordByEmail']);
Route::post('/user/{email}', [UsersController::class, 'listEmail']);

// Consultas confirm routes
Route::get('/consultas', [ConsultasController::class, 'index']);

// Notificações confirm routes
Route::get('/notificacoes', [NotificationController::class, 'index']);

// Notificações routes
    Route::prefix('notificacoes')->group(function () {
        Route::post('/search', [NotificationController::class, 'search']);         // Buscar notificações visíveis
        Route::post('/',       [NotificationController::class, 'store']);          // Criar nova notificação
        Route::post('/lidas',  [NotificationController::class, 'markAllRead']);    // Marcar todas como lidas
    });

// Manifesto confirm routes
Route::get('/manifesto', [ManifestoController::class, 'index']);

// Acompanhamento confirm routes
Route::get('/acompanhamento', [AcompanhamentoController::class, 'index']);

// Lembrete confirm routes
Route::get('/lembrete', [LembreteController::class, 'index']);

// Motoristas confirm routes
Route::get('/motoristas', [MotoristasController::class, 'index']);

// Cargas confirm routes
Route::get('/cargas', [CargasController::class, 'index']);

// Mensagens confirm route
Route::get('/mensagens', [MensagensController::class, 'index']);

// Mensagens confirm route
Route::get('/rastreio', [RastreioController::class, 'index']);

// #AUTENTICATION ROUTES

Route::middleware('auth:sanctum')->group(function() {

    // Users routes
    Route::prefix('users')->group(function () {
        Route::get('/s', [UsersController::class, 'search']);
        Route::get('/{id}', [UsersController::class, 'filter']);
        Route::post('/', [UsersController::class, 'cad']);
        Route::put('/{id}', [UsersController::class, 'edit']);
        Route::put('/{id}/change', [UsersController::class, 'changePassword']);
        Route::delete('/{id}', [UsersController::class, 'delete']);
        
    
    });
    
    // Consultas routes
    Route::prefix('consultas')->group(function () {
        Route::get('/s', [ConsultasController::class, 'search']);
        Route::get('/{id}', [ConsultasController::class, 'filter']);
        Route::post('/', [ConsultasController::class, 'cad']);
        Route::put('/{id}', [ConsultasController::class, 'edit']);
        Route::delete('/{id}', [ConsultasController::class, 'delete']);
        Route::delete('/del', [ConsultasController::class, 'deleteEnviados']);
        Route::delete('/clear', [ConsultasController::class, 'destroy']);

    });

    // Manifesto routes
    Route::prefix('manifesto')->group(function () {
        Route::get('/s', [ManifestoController::class, 'search']);
        Route::get('/{id}', [ManifestoController::class, 'filter']);
        Route::post('/', [ManifestoController::class, 'cad']);
        Route::put('/{id}', [ManifestoController::class, 'edit']);
        Route::delete('/{id}', [ManifestoController::class, 'delete']);

    });

    // Acompanhamento routes
    Route::prefix('acompanhamento')->group(function () {
        Route::get('/s', [AcompanhamentoController::class, 'search']);
        Route::get('/{id}', [AcompanhamentoController::class, 'filter']);
        Route::post('/', [AcompanhamentoController::class, 'cad']);
        Route::put('/{id}', [AcompanhamentoController::class, 'edit']);
        Route::delete('/{id}', [AcompanhamentoController::class, 'delete']);

    });

    // Lembrete routes
    Route::prefix('lembrete')->group(function () {
        Route::post('/s', [LembreteController::class, 'search']);
        Route::get('/{id}', [LembreteController::class, 'filter']);
        Route::post('/', [LembreteController::class, 'cad']);
        Route::put('/{id}', [LembreteController::class, 'edit']);
        Route::patch('/{id}/done',  [LembreteController::class, 'done']);
        Route::delete('/{id}', [LembreteController::class, 'delete']);

    });

    // Motoristas routes
    Route::prefix('motoristas')->group(function () {
        Route::get('/s', [MotoristasController::class, 'search']);
        Route::get('/{id}', [MotoristasController::class, 'filter']);
        Route::post('/', [MotoristasController::class, 'cad']);
        Route::put('/{id}', [MotoristasController::class, 'edit']);
        Route::delete('/{id}', [MotoristasController::class, 'delete']);

    });

    // Cargas routes
    Route::prefix('cargas')->group(function () {
        Route::get('/s', [CargasController::class, 'search']);
        Route::get('/{id}', [CargasController::class, 'filter']);
        Route::post('/', [CargasController::class, 'cad']);
        Route::put('/{id}', [CargasController::class, 'edit']);
        Route::delete('/{id}', [CargasController::class, 'delete']);

    });

    // Mensagens routes com prefixo 'mensagens'
    Route::prefix('mensagens')->group(function () {
        Route::get('/s', [MensagensController::class, 'search']);
        Route::post('/', [MensagensController::class, 'cad']);
        Route::delete('/{id}', [MensagensController::class, 'delete']);
    });

    // Rastreio routes
    Route::prefix('rastreio')->group(function () {
        Route::get('/s', [RastreioController::class, 'search']);
        Route::get('/{id}', [RastreioController::class, 'filter']);
        Route::post('/', [RastreioController::class, 'cad']);
        Route::put('/{id}', [RastreioController::class, 'edit']);
        Route::delete('/{id}', [RastreioController::class, 'delete']);

    });

});

// ##### ROTAS DE REFERÊNCIA #####

// Estados routes
Route::get('/estados', [EstadosController::class, 'index']);
Route::prefix('estados')->group(function () {
    Route::get('/s', [EstadosController::class, 'search']);
    Route::get('/{id}', [EstadosController::class, 'filter']);
    Route::post('/filter', [EstadosController::class, 'filterEstados']);
    Route::post('/', [EstadosController::class, 'cad']);
    Route::put('/{id}', [EstadosController::class, 'edit']);
    Route::delete('/{id}', [EstadosController::class, 'delete']);

});

// Requisitos routes
Route::get('/req', [RequisitosController::class, 'index']);
Route::prefix('req')->group(function () {
    Route::get('/s', [RequisitosController::class, 'search']);
    Route::get('/{id}', [RequisitosController::class, 'filter']);
    Route::post('/filter', [RequisitosController::class, 'filterRequisitos']);
    Route::post('/', [RequisitosController::class, 'cad']);
    Route::put('/{id}', [RequisitosController::class, 'edit']);
    Route::delete('/{id}', [RequisitosController::class, 'delete']);

});

// Carrocerias routes
Route::get('/car', [CarroceriaController::class, 'index']);
Route::prefix('car')->group(function () {
    Route::get('/s', [CarroceriaController::class, 'search']);
    Route::get('/{id}', [CarroceriaController::class, 'filter']);
    Route::post('/filter', [CarroceriaController::class, 'filterCarrocerias']);
    Route::post('/', [CarroceriaController::class, 'cad']);
    Route::put('/{id}', [CarroceriaController::class, 'edit']);
    Route::delete('/{id}', [CarroceriaController::class, 'delete']);

});

// Veiculos routes
Route::get('/vei', [VeiculosController::class, 'index']);
Route::prefix('vei')->group(function () {
    Route::get('/s', [VeiculosController::class, 'search']);
    Route::get('/{id}', [VeiculosController::class, 'filter']);
    Route::post('/filter', [VeiculosController::class, 'filterVeiculos']);
    Route::post('/', [VeiculosController::class, 'cad']);
    Route::put('/{id}', [VeiculosController::class, 'edit']);
    Route::delete('/{id}', [VeiculosController::class, 'delete']);

});

// Clientes confirm routes
Route::get('/clis', [ClientesController::class, 'index']);

// Filiais confirm routes
Route::get('/fli', [FilialController::class, 'index']);

// CDs Clientes confirm routes
Route::get('/cds', [FilialController::class, 'index']);

Route::middleware('auth:sanctum')->group(function() {

    // Clientes routes
    Route::prefix('clis')->group(function () {
        Route::get('/s', [ClientesController::class, 'search']);
        Route::get('/{id}', [ClientesController::class, 'filter']);
        Route::post('/filter', [ClientesController::class, 'filterClientes']);
        Route::post('/', [ClientesController::class, 'cad']);
        Route::put('/{id}', [ClientesController::class, 'edit']);
        Route::delete('/{id}', [ClientesController::class, 'delete']);
    });

    // Filiais routes
    Route::prefix('fli')->group(function () {
        Route::get('/s', [FilialController::class, 'search']);
        Route::get('/{id}', [FilialController::class, 'filter']);
        Route::post('/filter', [FilialController::class, 'filterFilial']);
        Route::post('/', [FilialController::class, 'cad']);
        Route::put('/{id}', [FilialController::class, 'edit']);
        Route::delete('/{id}', [FilialController::class, 'delete']);
    });

    // CD Clientes routes
    Route::prefix('cds')->group(function () {
        Route::get('/s', [CdClientesController::class, 'search']);
        Route::get('/{id}', [CdClientesController::class, 'filter']);
        Route::post('/filter', [CdClientesController::class, 'filterCds']);
        Route::post('/', [CdClientesController::class, 'cad']);
        Route::put('/{id}', [CdClientesController::class, 'edit']);
        Route::delete('/{id}', [CdClientesController::class, 'delete']);
    });

});

Route::middleware('auth:sanctum')->group(function() {

    // Contatos routes
    Route::prefix('contatos')->group(function () {
        Route::get('/s', [ContatosController::class, 'search']);
        Route::get('/{id}', [ContatosController::class, 'filter']);
        Route::post('/', [ContatosController::class, 'cad']);
        Route::put('/{id}', [ContatosController::class, 'edit']);
        Route::delete('/{id}', [ContatosController::class, 'delete']);
        Route::delete('/clear', [ContatosController::class, 'destroy']);
    });

    // Links routes
    Route::prefix('links')->group(function () {
        Route::get('/s', [LinksController::class, 'search']);
        Route::get('/{id}', [LinksController::class, 'filter']);
        Route::post('/', [LinksController::class, 'cad']);
        Route::put('/{id}', [LinksController::class, 'edit']);
        Route::delete('/{id}', [LinksController::class, 'delete']);
        Route::delete('/clear', [LinksController::class, 'destroy']);
    });

});