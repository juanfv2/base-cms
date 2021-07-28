<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('authenticate',                                    [App\Http\Controllers\API\Auth\ZLoginAPIController::class, 'login'])->name('login.login');

Route::post('register',                                        [App\Http\Controllers\API\Auth\ZRegisterAPIController::class, 'register'])->name('register.register');
Route::post('user/verify/{token}',                             [App\Http\Controllers\API\Auth\ZRegisterAPIController::class, 'verifyUser'])->name('register.verifyUser');

Route::post('password/email',                                  [App\Http\Controllers\API\Auth\ZForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('password/reset',                                  [App\Http\Controllers\API\Auth\ZResetPasswordController::class, 'reset'])->name('password.reset');

Route::get('file/{tableName}/{fieldName}/{id}/{w?}/{h?}/{n?}', [App\Http\Controllers\API\Auth\XFileAPIController::class, 'fileDown'])->name('x_files.down');

Route::get('companies/cname/{cname}',                          [App\Http\Controllers\API\CompanyAPIController::class, 'getByName'])->name('company.name');

// Route::middleware(['cors', 'sentry_context'])->group(function () {
//     Route::post('user/verify/{token}',   '\Juanfv2\BaseCms\Controllers\Auth\ZRegisterAPIController@verifyUser')->name('register.verifyUser');
//     Route::post('password/reset',        '\Juanfv2\BaseCms\Controllers\Auth\ZResetPasswordController@reset')->name('password.reset');
// });

// ADMIN ----------------------------------- //
Route::middleware(['auth:api', 'role', 'sentry_context'])->group(function () {

    Route::apiResource('roles',                                Auth\RoleAPIController::class);
    Route::apiResource('users',                                Auth\UserAPIController::class);
    Route::apiResource('x_files',                              Auth\XFileAPIController::class);

    // Route::post('subscribe',                                   SingleAction\FirebaseSubscriber::class)->name('firebase.subscriber');

    Route::post('logout',                                      [App\Http\Controllers\API\Auth\ZLoginAPIController::class, 'logout'])->name('login.logout');
    Route::post('import-csv',                                  [App\Http\Controllers\API\Auth\XFileAPIController::class, 'importCsv'])->name('x_files.csv');
    Route::post('file/{tableName}/{fieldName}/{id?}/{color?}', [App\Http\Controllers\API\Auth\XFileAPIController::class, 'fileUpload'])->name('x_files.upload');
    Route::get('permissions',                                  [App\Http\Controllers\API\Auth\RoleAPIController::class, 'permissions']);
    Route::get('visor_list_file_versions/downloadable/{id}',   [App\Http\Controllers\API\VisorListFileVersionAPIController::class, 'downloadable'])->name('version.downloadable');

    /******************/
    /* start entities */
    /******************/
    Route::apiResource('countries',                             Country\CountryAPIController::class);
    Route::apiResource('regions',                               Country\RegionAPIController::class);
    Route::apiResource('cities',                                Country\CityAPIController::class);
    /****************/
    /* end entities */
    /****************/
});
// ADMIN ----------------------------------- //
