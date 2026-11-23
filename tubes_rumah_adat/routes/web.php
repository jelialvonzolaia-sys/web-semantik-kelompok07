<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RumahAdatController;

// Homepage
Route::get('/', function () {
    return view('pages.home');
})->name('home');

// Search Results
Route::get('/search', [RumahAdatController::class, 'index'])
    ->name('search');

// Detail Rumah Adat
Route::get('/detail/{slug}', [RumahAdatController::class, 'show'])
    ->name('detail');