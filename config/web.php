<?php

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// requeridos por laravel para verificar correos

Route::get('/email/verify/{id}', function ($id) {
    Auth::loginUsingId($id);

    return 1;
})->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/');
})->middleware(['auth', 'signed'])->name('verification.verify');

// queue:flush   >> /a/r/t/-/queue/flush
// config:clear  >> /a/r/t/-/config/clear

/* uncomment only by execute * /
Route::get('/a/r/t/-/{key1?}/{key2?}', [\App\Http\Controllers\AdminController::class, 'artisan'])->name('project.admin.artisan');
/* */

Route::get('admin/dev/{country?}/{controller?}/{action?}/{id?}/{any1?}/{any2?}/{any3?}', (new \App\Http\Controllers\AdminController())->adminDevelopment(...))->name('project.admin.dev');
Route::get('admin/{country?}/{controller?}/{action?}/{id?}/{any1?}/{any2?}/{any3?}', (new \App\Http\Controllers\AdminController())->admin(...))->name('project.admin');

// ? no web content?, so direct to admin
Route::get('/', (new \App\Http\Controllers\AdminController())->admin(...))->name('project.web');
// ? web content, react, vue, etc
// Route::get('/', [\App\Http\Controllers\WebController::class, 'web'])->name('project.web');
