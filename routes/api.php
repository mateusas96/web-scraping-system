<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth', 'validateBackHistory']], function(){
    
});

Route::group(['middleware' => ['auth', 'validateBackHistory', 'checkIfAdmin']], function() {
    Route::apiResources([
        'user' => 'API\UserController',
        'file' => 'API\FileController',
    ]);

    Route::post('updateFile/{uuid}', 'API\FileController@updateFile')->name('update.file');

    Route::get('findUser','API\UserController@search')->name('find.user');
});
