<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;


Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/sync', [DashboardController::class, 'sync'])->name('sync');


