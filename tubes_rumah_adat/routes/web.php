<?php

use App\Http\Controllers\RumahAdatController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RumahAdatController::class, 'index'])
    ->name('home');

Route::get('/search', [RumahAdatController::class, 'search'])
    ->name('search');

Route::get('/detail/{slug}', [RumahAdatController::class, 'detail'])
    ->name('detail');

Route::get('/graph', [RumahAdatController::class, 'graph'])
    ->name('graph');

Route::get('/api/graph', [RumahAdatController::class, 'apiGraph'])
    ->name('api.graph');
