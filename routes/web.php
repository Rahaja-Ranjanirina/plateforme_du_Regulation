<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArtecController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OperateurController;
use App\Http\Controllers\VisiteurController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

// Authentification & Inscription
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login'])->name('login.store');

    Route::get('/inscription', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/inscription', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/deconnexion', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

// 1. Espace Administrateur (Gère les comptes ARTEC et Opérateurs)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
});

// 2. Espace ARTEC (Régulation des 3 opérateurs, sondages, avis, avertissements, IA)
Route::middleware('auth')->prefix('artec')->name('artec.')->group(function () {
    Route::get('/dashboard', [ArtecController::class, 'index'])->name('dashboard');
    Route::post('/sondages', [ArtecController::class, 'storeSondage'])->name('sondages.store');
    Route::post('/messages', [ArtecController::class, 'sendMessage'])->name('messages.store');
});

// 3. Espace Opérateur (Yas, Airtel, Orange)
Route::middleware('auth')->prefix('operateur')->name('operateur.')->group(function () {
    Route::get('/dashboard', [OperateurController::class, 'index'])->name('dashboard');
    Route::post('/sondages', [OperateurController::class, 'storeSondage'])->name('sondages.store');
    Route::post('/actualites', [OperateurController::class, 'storeActualite'])->name('actualites.store');
    Route::post('/offres', [OperateurController::class, 'storeOffre'])->name('offres.store');
    Route::post('/messages/{message}/lu', [OperateurController::class, 'markMessageRead'])->name('messages.read');
});

// 4. Espace Visiteur (Offres, Actualités avec liens, Sondages et avis sur SMS, Voix, Internet, Crédit, Satisfait)
Route::middleware('auth')->prefix('visiteur')->name('visiteur.')->group(function () {
    Route::get('/dashboard', [VisiteurController::class, 'index'])->name('dashboard');
    Route::post('/reponses', [VisiteurController::class, 'storeReponse'])->name('reponses.store');
});
