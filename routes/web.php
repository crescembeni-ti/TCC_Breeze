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
Route::get('/contato', [ContactController::class, 'index'])->name('contact');
Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');
Route::post('/contato/denuncia', [ReportController::class, 'store'])->middleware('auth')->name('report.store');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rotas de administrador
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard/map', [TreeController::class, 'adminMap'])->name('admin.map');
    Route::post('/dashboard/map', [TreeController::class, 'storeTree'])->name('admin.map.store');
});

require __DIR__.'/auth.php';
