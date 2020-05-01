<?php

use Illuminate\Http\Request;

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
Route::prefix('auth')->group(function () {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    Route::get('refresh', 'AuthController@refresh');

    Route::middleware('auth')->group(function () {
        Route::get('me', 'AuthController@me');
        Route::post('logout', 'AuthControoler@logout');
    });
});

Route::middleware('auth')->group(function() {
    Route::get('users/{id}', 'UsersController@show');
    Route::resource('files', 'FilesController');
    Route::middleware('checkArticleAccess')
        ->resource('articles', 'ArticlesController');
});
