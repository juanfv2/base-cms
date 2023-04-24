<?php

use Illuminate\Support\Facades\Route;

Route::post('login', (new App\Http\Controllers\API\Auth\ZLoginAPIController())->login(...))->name('login.login');

Route::post('register', [App\Http\Controllers\API\Auth\ZRegisterAPIController::class, 'register'])->name('register.register');
Route::post('user/verify/{token}', [App\Http\Controllers\API\Auth\ZRegisterAPIController::class, 'verifyUser'])->name('register.verifyUser');

Route::post('password/email', (new App\Http\Controllers\API\Auth\ZForgotPasswordController())->sendResetLinkEmail(...))->name('password.email');
Route::post('password/reset', (new App\Http\Controllers\API\Auth\ZResetPasswordController())->reset(...))->name('password.reset');
Route::post('visor-log-errors-index', [App\Http\Controllers\API\Misc\VisorLogErrorAPIController::class, 'store'])->name('errors.index');

Route::get('file/{tableName}/{fieldName}/{id}/{w?}/{h?}/{n?}', [App\Http\Controllers\API\Misc\XFileAPIController::class, 'fileDown'])->name('x_files.down');

// ADMIN ----------------------------------- //
Route::middleware(['auth:sanctum', 'role', 'sentry.context'])->group(function () {
    Route::apiResource('roles', \App\Http\Controllers\API\Auth\RoleAPIController::class);
    Route::apiResource('users', \App\Http\Controllers\API\Auth\UserAPIController::class);

    // Route::post('subscribe',                                   SingleAction\FirebaseSubscriber::class)->name('firebase.subscriber');

    Route::post('logout', (new App\Http\Controllers\API\Auth\ZLoginAPIController())->logout(...))->name('login.logout');
    Route::get('permissions', [App\Http\Controllers\API\Auth\RoleAPIController::class, 'permissions'])->name('roles.permissions');

    Route::post('seeder', [App\Http\Controllers\API\Misc\XFileAPIController::class, 'seeder'])->name('seeder.upload');
    Route::post('import-csv', [App\Http\Controllers\API\Misc\XFileAPIController::class, 'importCsv'])->name('x_files.csv');
    Route::post('file/{tableName}/{fieldName}/{id?}/{color?}', [App\Http\Controllers\API\Misc\XFileAPIController::class, 'fileUpload'])->name('x_files.upload');
    Route::apiResource('x-files', App\Http\Controllers\API\Misc\XFileAPIController::class);
    Route::apiResource('visor-log-errors', App\Http\Controllers\API\Misc\VisorLogErrorAPIController::class);
    /******************/
    /* start entities */
    /******************/
    Route::apiResource('countries', \App\Http\Controllers\API\Country\CountryAPIController::class);
    Route::apiResource('regions', \App\Http\Controllers\API\Country\RegionAPIController::class);
    Route::apiResource('cities', \App\Http\Controllers\API\Country\CityAPIController::class);

    Route::apiResource('tracking-jobs', App\Http\Controllers\API\TrackingJobAPIController::class);
    /****************/
    /* end entities */
    /****************/
});
// ADMIN ----------------------------------- //
