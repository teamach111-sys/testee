<?php

use Illuminate\Support\Facades\Route;

// Page d'accueil du backend (diagnostic)
Route::get('/', function () {
    return view('welcome');
});

// Route nommée 'login' : fallback utilisé par le middleware d'authentification
// quand le client ne demande pas du JSON. Retourne un 401 JSON (API uniquement).
Route::get('/login', function () {
    return response()->json([
        'success' => false,
        'message' => 'Non authentifié.',
        'data'    => null,
        'errors'  => ['auth' => 'Token invalide ou manquant.'],
    ], 401);
})->name('login');
