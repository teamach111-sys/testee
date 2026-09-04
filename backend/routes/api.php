<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\CandidatureController;
use App\Http\Controllers\CandidatController;
use App\Http\Controllers\EntretienController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\StatsController;

/*
|--------------------------------------------------------------------------
| API Routes — Greativa HR Portal
|--------------------------------------------------------------------------
*/

// ─── AUTH ────────────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login',          [AdminAuthController::class, 'login']);
    Route::post('/forgot-password',[AdminAuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AdminAuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout',     [AdminAuthController::class, 'logout']);
        Route::get('/me',          [AdminAuthController::class, 'me']);
    });
});

// ─── DEPARTEMENTS (public + admin) ───────────────────────────────────────────
Route::get('/departements', [DepartementController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/departements',           [DepartementController::class, 'store']);
    Route::put('/departements/{id}',       [DepartementController::class, 'update']);
    Route::delete('/departements/{id}',    [DepartementController::class, 'destroy']);
});

// ─── OFFRES ───────────────────────────────────────────────────────────────────
Route::get('/offres',       [OffreController::class, 'index']);  // public
Route::get('/offres/{id}',  [OffreController::class, 'show']);   // public

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/offres',                        [OffreController::class, 'store']);
    Route::put('/offres/{id}',                    [OffreController::class, 'update']);
    Route::patch('/offres/{id}/toggle-publish',   [OffreController::class, 'togglePublish']);
    Route::patch('/offres/{id}/archive',          [OffreController::class, 'archive']);
    Route::delete('/offres/{id}',                 [OffreController::class, 'destroy']);
});

// ─── CANDIDATURES ─────────────────────────────────────────────────────────────
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/candidatures', [CandidatureController::class, 'store']); // public
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/candidatures/export',          [CandidatureController::class, 'export']);
    Route::get('/candidatures',                 [CandidatureController::class, 'index']);
    Route::get('/candidatures/{id}',            [CandidatureController::class, 'show']);
    Route::patch('/candidatures/{id}/statut',   [CandidatureController::class, 'updateStatut']);
    Route::patch('/candidatures/{id}/note',     [CandidatureController::class, 'updateNote']);
    Route::delete('/candidatures/{id}',         [CandidatureController::class, 'destroy']);
});

// ─── CANDIDATS ────────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/candidats',           [CandidatController::class, 'index']);
    Route::get('/candidats/{id}',      [CandidatController::class, 'show']);
    Route::delete('/candidats/{id}',   [CandidatController::class, 'destroy']);
    Route::patch('/candidats/{id}/note', [CandidatController::class, 'updateNote']);
    Route::get('/candidats/{id}/cv',   [CandidatController::class, 'downloadCv']);
});

// ─── ENTRETIENS ───────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/entretiens',                       [EntretienController::class, 'index']);
    Route::post('/entretiens',                      [EntretienController::class, 'store']);
    Route::get('/entretiens/{id}',                  [EntretienController::class, 'show']);
    Route::put('/entretiens/{id}',                  [EntretienController::class, 'update']);
    Route::patch('/entretiens/{id}/resultat',       [EntretienController::class, 'updateResultat']);
    Route::delete('/entretiens/{id}',               [EntretienController::class, 'destroy']);
});

// ─── ARCHIVES ─────────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/archives',                        [ArchiveController::class, 'index']);
    Route::post('/archives/{id}/restaurer',        [ArchiveController::class, 'restaurer']);
    Route::delete('/archives/{id}',               [ArchiveController::class, 'destroy']);
});

// ─── STATS / DASHBOARD ────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/stats/overview', [StatsController::class, 'overview']);
});
