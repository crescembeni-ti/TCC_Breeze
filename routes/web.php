<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\VerifyEmailCodeController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\AnalystLoginController; 
use Illuminate\Support\Facades\Route;
use App\Models\Bairro;

// ADMIN controllers
use App\Http\Controllers\Admin\AuthenticatedSessionController as AdminLoginController;
use App\Http\Controllers\Admin\AdminProfileController;

// ANALISTA controllers
use App\Http\Controllers\Analista\AuthenticatedSessionController as AnalystLoginController;

// SERVIÇO controllers
use App\Http\Controllers\Servico\AuthenticatedSessionController as ServicoLoginController;


/*
|--------------------------------------------------------------------------
| 🌍 ROTAS PÚBLICAS (com preventBack)
|--------------------------------------------------------------------------
*/
Route::middleware('preventBack')->group(function () {
    Route::get('/', [TreeController::class, 'index'])->name('home');
    Route::get('/api/trees', [TreeController::class, 'getTreesData'])->name('trees.data');
    Route::get('/trees/{id}', [TreeController::class, 'show'])->name('trees.show');
    Route::get('/sobre', [PageController::class, 'about'])->name('about');
    Route::get('/bairros/data', fn() => response()->json(Bairro::all()))->name('bairros.data');
});


/*
|--------------------------------------------------------------------------
| 📣 Envio de denúncia (somente usuários logados)
|--------------------------------------------------------------------------
*/
Route::post('/contato/denuncia', [ReportController::class, 'store'])
    ->middleware(['auth', 'preventBack'])
    ->name('report.store');


/*
|--------------------------------------------------------------------------
| 👤 PERFIL DO USUÁRIO
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'preventBack'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| 🟩 ÁREA DO USUÁRIO VERIFICADO
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'preventBack'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/contato', [ContactController::class, 'index'])->name('contact');
    Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');
    Route::get('/minhas-solicitacoes', [ContactController::class, 'userRequestList'])->name('contact.myrequests');
    Route::patch('/minhas-solicitacoes/{contact}/cancelar', [ContactController::class, 'cancelRequest'])->name('contact.cancel');
});


/*
|--------------------------------------------------------------------------
| 🔐 ROTAS ADMINISTRATIVAS (/pbi-admin)
|--------------------------------------------------------------------------
| Guard: admin | Provider: admins | Model: App\Models\Admin
*/
Route::prefix('pbi-admin')->name('admin.')->group(function () {

    Route::get('/', fn() => redirect()->route('admin.login'));

    // Login admin
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
        // CORRIGIDO: Nome da rota de submissão do formulário
        Route::post('/login', [AdminLoginController::class, 'store'])->name('login.store'); 
    });

    // Área protegida admin
    Route::middleware(['auth:admin', 'preventBack'])->group(function () {

        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');

        Route::get('/dashboard', [TreeController::class, 'adminDashboard'])->name('dashboard');
        Route::get('/map', [TreeController::class, 'adminMap'])->name('map');
        Route::post('/map', [TreeController::class, 'storeTree'])->name('map.store');

        Route::get('/trees', [TreeController::class, 'adminTreeList'])->name('trees.index');
        Route::get('/trees/{tree}/edit', [TreeController::class, 'adminTreeEdit'])->name('trees.edit');
        Route::patch('/trees/{tree}', [TreeController::class, 'adminTreeUpdate'])->name('trees.update');
        Route::delete('/trees/{tree}', [TreeController::class, 'adminTreeDestroy'])->name('trees.destroy');

        // Contatos
        Route::get('/contacts', [ContactController::class, 'adminContactList'])->name('contato.index');
        Route::patch('/contacts/{contact}', [ContactController::class, 'adminContactUpdateStatus'])->name('contacts.updateStatus');

        // Notícias
        Route::get('/noticias', [NoticiaController::class, 'index'])->name('noticias.index');
        Route::get('/noticias/create', [NoticiaController::class, 'create'])->name('noticias.create');
        Route::post('/noticias', [NoticiaController::class, 'store'])->name('noticias.store');

        // Perfil admin
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [AdminProfileController::class, 'destroy'])->name('profile.destroy');
    });
});


/*
|--------------------------------------------------------------------------
| 🧪 ROTAS DO ANALISTA (/pbi-analista)
|--------------------------------------------------------------------------
| Guard: analyst | Provider: analysts | Model: App\Models\Analyst
*/
Route::prefix('pbi-analista')->name('analyst.')->group(function () {

    // Redireciona /pbi-analista → /pbi-analista/login
    Route::get('/', function () {
        return redirect()->route('analyst.login');
    });

    // Login do analista (apenas guest)
    Route::middleware('guest:analyst')->group(function () {
        Route::get('/login', [AnalystLoginController::class, 'create'])->name('login');
        // CORRIGIDO: Nome da rota de submissão do formulário (USADO NA VIEW)
        Route::post('/login', [AnalystLoginController::class, 'store'])->name('login.store');
    });

    // Área protegida do analista
    Route::middleware(['auth:analyst', 'preventBack'])->group(function () {
        Route::post('/logout', [AnalystLoginController::class, 'destroy'])->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/map', [TreeController::class, 'adminMap'])->name('map');
    });
});



/*
|--------------------------------------------------------------------------
| 🛠 ROTAS DO SERVIÇO (/pbi-servico)
|--------------------------------------------------------------------------
| Guard: service | Provider: services | Model: App\Models\Service
*/
Route::prefix('pbi-servico')->name('service.')->group(function () {

    // Redireciona /pbi-servico → /pbi-servico/login
    Route::get('/', function () {
        return redirect()->route('service.login');
    });
    
    // Login serviço (guest)
    Route::middleware('guest:service')->group(function () {
        Route::get('/login', [ServicoLoginController::class, 'create'])->name('login');
        // CORRIGIDO: Nome da rota de submissão do formulário
        Route::post('/login', [ServicoLoginController::class, 'store'])->name('login.store');
    });

    // Área protegida do serviço
    Route::middleware(['auth:service', 'preventBack'])->group(function () {
        Route::post('/logout', [ServicoLoginController::class, 'destroy'])->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/map', [TreeController::class, 'adminMap'])->name('map');
    });
});



/*
|--------------------------------------------------------------------------
| 🔑 ROTAS DE AUTENTICAÇÃO BREEZE
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';