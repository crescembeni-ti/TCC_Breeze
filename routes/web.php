<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TreeController::class, 'index'])->name('home');
Route::get('/api/trees', [TreeController::class, 'getTreesData'])->name('trees.data');
Route::get('/trees/{id}', [TreeController::class, 'show'])->name('trees.show');
Route::get('/sobre', [PageController::class, 'about'])->name('about');

// Rota de Denúncia/Reporte (já estava protegida)
Route::post('/contato/denuncia', [ReportController::class, 'store'])->middleware('auth')->name('report.store');

// Rota Antiga de Contato (Vou movê-la para o grupo 'auth' abaixo, pois agora requer login)
// Route::get('/contato', [ContactController::class, 'index'])->name('contact');
// Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// --- GRUPO DE ROTAS PROTEGIDAS POR AUTENTICAÇÃO ---
Route::middleware('auth')->group(function () {
    // ROTAS DE PERFIL
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // ROTAS DE SOLICITAÇÃO (CONTACT) - Agora protegidas!
    // Apenas usuários logados podem ver o formulário e enviar a solicitação.
    Route::get('/contato', [ContactController::class, 'index'])->name('contact');
    Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');
});

Route::middleware(['auth' ,'verified'])->group(function () {
    Route::get('/dashboard', function(){
        return view ('dashboard');      
    })-> name('dashboard');
});

// Rotas de administrador
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard/map', [TreeController::class, 'adminMap'])->name('admin.map');
    Route::post('/dashboard/map', [TreeController::class, 'storeTree'])->name('admin.map.store');
    Route::get('/dashboard/trees', [TreeController::class, 'adminTreeList'])->name('admin.trees.index');
    Route::get('/dashboard/trees/{tree}/edit', [TreeController::class, 'adminTreeEdit'])->name('admin.trees.edit');
    Route::patch('/dashboard/trees/{tree}', [TreeController::class, 'adminTreeUpdate'])->name('admin.trees.update');
    Route::get('/dashboard/contacts', [ContactController::class, 'adminContactList'])->name('admin.contacts.index');
    Route::patch('/dashboard/contacts/{contact}', [ContactController::class, 'adminContactUpdateStatus'])->name('admin.contacts.updateStatus');
});

require __DIR__.'/auth.php';