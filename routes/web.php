<?php

use Illuminate\Support\Facades\Route;

// Rota da página inicial
Route::view('/', 'welcome');

// Rota do Dashboard (Protegida)
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Rota do Perfil de Usuário (Protegida)
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// --- ROTAS DO AGOTA ---

// Rota de Categorias (Protegida)
Route::view('categories', 'categories')
    ->middleware(['auth'])
    ->name('categories');

// Rota de Investimentos (Protegida)
Route::view('investments', 'investments')
    ->middleware(['auth'])
    ->name('investments');

// Puxa as rotas de login/senha geradas pelo Laravel
require __DIR__.'/auth.php';