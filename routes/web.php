<?php

use Illuminate\Support\Facades\Route;

/**
 * ARQUIVO DE ROTAS PRINCIPAL
 * Aqui são definidas todas as URLs do site e quais controladores cuidam de cada uma.
 */

/*
|--------------------------------------------------------------------------
| CONTROLLERS - IMPORTAÇÃO
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController; 
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\AboutPageController;
use App\Http\Controllers\Admin\AuthenticatedSessionController as AdminLoginController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AccountManagementController;
use App\Http\Controllers\Admin\AdminServiceController;
use App\Http\Controllers\ServiceOrderController;
use App\Http\Controllers\Analista\AuthenticatedSessionController as AnalystLoginController;
use App\Http\Controllers\Servico\AuthenticatedSessionController as ServiceLoginController;
use App\Http\Controllers\Servico\ServiceExecutionController;
use App\Http\Controllers\Servico\DashboardController as ServiceDashboardController;
use App\Http\Controllers\SpeciesController;
use App\Http\Controllers\Auth\VerifyEmailCodeController;
use App\Models\Bairro;

/*
|--------------------------------------------------------------------------
| ROTAS PÚBLICAS (Acessíveis por qualquer pessoa)
|--------------------------------------------------------------------------
*/
Route::middleware('preventBack')->group(function () {
    Route::get('/', [TreeController::class, 'index'])->name('home'); // Página inicial com o mapa
    Route::get('/api/trees', [TreeController::class, 'getTreesData'])->name('trees.data'); // Dados JSON para o mapa
    Route::get('/trees/{id}', [TreeController::class, 'show'])->name('trees.show'); // Detalhes de uma árvore
    Route::get('/sobre', [AboutPageController::class, 'index'])->name('about'); // Página "Sobre"
    Route::get('/bairros/data', fn () => response()->json(Bairro::all()))->name('bairros.data'); // Lista de bairros
});

/*
|--------------------------------------------------------------------------
| VERIFICAÇÃO DE E-MAIL (Segurança no cadastro)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/verification/code', [VerifyEmailCodeController::class, 'show'])->name('verification.code.show');
    Route::post('/verification/code', [VerifyEmailCodeController::class, 'verify'])->name('verification.code.verify');
    Route::post('/verification/code/resend', [VerifyEmailCodeController::class, 'resend'])->name('verification.code.resend');
});

/*
|--------------------------------------------------------------------------
| USUÁRIO LOGADO (Cidadão)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:web', 'preventBack'])->group(function () {
    Route::post('/contato/denuncia', [ReportController::class, 'store'])->name('report.store'); // Fazer denúncia

    // Gerenciamento de Perfil do Usuário
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

// Rotas que exigem e-mail verificado
Route::middleware(['auth:web', 'verified', 'preventBack'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard'); // Painel do cidadão
    Route::get('/contato', [ContactController::class, 'index'])->name('contact'); // Formulário de solicitação
    Route::post('/contato', [ContactController::class, 'store'])->name('contact.store'); // Salvar solicitação
    Route::get('/minhas-solicitacoes', [ContactController::class, 'userRequestList'])->name('contact.myrequests'); // Ver pedidos
    Route::delete('/minhas-solicitacoes/{contact}/cancelar', [ContactController::class, 'cancelRequest'])->name('contact.cancel'); // Cancelar pedido
});

/*
|--------------------------------------------------------------------------
| PAINEL ADMINISTRATIVO (/pbi-admin)
|--------------------------------------------------------------------------
*/
Route::prefix('pbi-admin')->name('admin.')->group(function () {

    Route::get('/', fn () => redirect()->route('admin.login'));

    // Login do Admin (Protegido contra força bruta)
    Route::middleware(['guest:admin', 'guard.only:admin'])->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'store'])->name('login.store')->middleware('throttle:login');
    });

    // Área Logada do Admin e Analista
    Route::middleware(['auth:admin,analyst', 'preventBack'])->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');
        Route::get('/dashboard', [TreeController::class, 'adminDashboard'])->name('dashboard');

        // Gerenciamento de Árvores (Admin e Analista)
        Route::get('/map', [TreeController::class, 'adminMap'])->name('map'); // Mapa de cadastro
        Route::post('/map', [TreeController::class, 'storeTree'])->name('map.store'); // Salvar árvore
        Route::get('/trees/export', [TreeController::class, 'exportTrees'])->name('trees.export'); // Exportar Excel
        Route::get('/trees', [TreeController::class, 'adminTreeList'])->name('trees.index'); // Lista de árvores
        Route::get('/trees/{tree}/edit', [TreeController::class, 'adminTreeEdit'])->name('trees.edit'); // Editar
        Route::patch('/trees/{tree}', [TreeController::class, 'adminTreeUpdate'])->name('trees.update'); // Atualizar
        Route::delete('/trees/{tree}', [TreeController::class, 'adminTreeDestroy'])->name('trees.destroy'); // Deletar

        // Gestão de Solicitações e Ordens de Serviço
        Route::get('/contacts', [ContactController::class, 'adminContactList'])->name('contato.index');
        Route::patch('/contacts/{contact}', [ContactController::class, 'adminContactUpdateStatus'])->name('contacts.updateStatus');
        Route::patch('/contacts/{contact}/forward', [ContactController::class, 'forward'])->name('contacts.forward');

        Route::get('/os', [AdminServiceController::class, 'index'])->name('os.index');
        Route::get('/os/{os}', [AdminServiceController::class, 'show'])->name('os.show');
        Route::put('/os/{os}', [AdminServiceController::class, 'update'])->name('os.update');

        // Gerenciamento de Contas (Apenas Admin cria outros Admins/Analistas)
        Route::prefix('accounts')->name('accounts.')->group(function () {
            Route::get('/', [AccountManagementController::class, 'index'])->name('index');
            Route::post('/store', [AccountManagementController::class, 'store'])->name('store');
            Route::delete('/delete/{type}/{id}', [AccountManagementController::class, 'destroy'])->name('destroy');
        });
    });
});

/*
|--------------------------------------------------------------------------
| PAINEL DO ANALISTA (/pbi-analista)
|--------------------------------------------------------------------------
*/
Route::prefix('pbi-analista')->name('analyst.')->group(function () {
    Route::get('/', fn () => redirect()->route('analyst.login'));

    Route::middleware(['guest:analyst', 'guard.only:analyst'])->group(function () {
        Route::get('/login', [AnalystLoginController::class, 'create'])->name('login');
        Route::post('/login', [AnalystLoginController::class, 'store'])->name('login.store')->middleware('throttle:login');
    });

    Route::middleware(['auth:analyst', 'preventBack'])->group(function () {
        Route::post('/logout', [AnalystLoginController::class, 'destroy'])->name('logout');
        Route::get('/dashboard', [ContactController::class, 'analystDashboard'])->name('dashboard');
        Route::get('/vistorias-pendentes', [ContactController::class, 'vistoriasPendentes'])->name('vistorias.pendentes');
        Route::post('/gerar-os', [ContactController::class, 'storeServiceOrder'])->name('os.store');
        Route::get('/map', [TreeController::class, 'analystMap'])->name('map');
    });
});

/*
|--------------------------------------------------------------------------
| PAINEL DA EQUIPE DE SERVIÇO (/pbi-servico)
|--------------------------------------------------------------------------
*/
Route::prefix('pbi-servico')->name('service.')->group(function () {
    Route::get('/', fn () => redirect()->route('service.login'));

    Route::middleware(['guest:service', 'guard.only:service'])->group(function () {
        Route::get('/login', [ServiceLoginController::class, 'create'])->name('login');
        Route::post('/login', [ServiceLoginController::class, 'store'])->name('login.store')->middleware('throttle:login');
    });

    Route::middleware(['auth:service', 'preventBack'])->group(function () {
        Route::post('/logout', [ServiceLoginController::class, 'destroy'])->name('logout');
        Route::get('/dashboard', [ServiceDashboardController::class, 'index'])->name('dashboard');
        
        // Execução de Tarefas
        Route::get('/tarefas/recebidas', [ServiceExecutionController::class, 'recebidas'])->name('tasks.recebidas');
        Route::post('/tarefas/{id}/confirmar', [ServiceExecutionController::class, 'confirmarRecebimento'])->name('tasks.confirmar');
        Route::post('/tarefas/{id}/concluir', [ServiceExecutionController::class, 'concluir'])->name('tasks.concluir');
    });
});

// Importa rotas de autenticação padrão do Breeze (Login de usuário comum)
require __DIR__ . '/auth.php';
