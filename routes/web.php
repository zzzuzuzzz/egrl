<?php

use App\Http\Controllers\ListOfShareholdersController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('list-of-shareholders',
    [ListOfShareholdersController::class, 'makeListOfShareholders']
)->middleware(['auth', 'verified'])->name('makeListOfShareholders');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
