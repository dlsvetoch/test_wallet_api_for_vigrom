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

Route::middleware('auth:api')->group(function () {
    Route::prefix('wallets')->group(function () {
		Route::get('/', ['uses' => 'WalletController@index']);
		Route::get('/{wallet}', ['uses' => 'WalletController@show']);
		Route::get('/{wallet}/balance', ['uses' => 'WalletController@showBalance']);
		Route::get('/{wallet}/transactions/sum', ['uses' => 'WalletController@showTransactionSum']);
		Route::post('/', ['uses' => 'WalletController@store']);
		Route::put('/{wallet}', ['uses' => 'WalletController@update']);
	});
	
	Route::prefix('users')->group(function () {
		Route::get('/{user}', ['uses' => 'UserController@show']);
		Route::post('/', ['uses' => 'UserController@store']);
	});

	Route::prefix('transactions')->group(function () {
		Route::get('/', ['uses' => 'TransactionController@index']);
	});

	Route::get('logout', ['uses' => 'AuthController@logout']);
});

Route::get('auth_error', ['uses' => 'AuthController@authError'])->name('auth_error');
Route::post('login', ['uses' => 'AuthController@login']);
Route::post('register', ['uses' => 'AuthController@register']);



