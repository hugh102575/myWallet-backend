<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web3Controller;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/getNetworkInfo', [Web3Controller::class, 'getNetworkInfo']);
Route::post('/getNetworkList', [Web3Controller::class, 'getNetworkList']);
Route::post('/getTokenPrice', [Web3Controller::class, 'getTokenPrice']);
Route::post('/getUserTokens', [Web3Controller::class, 'getUserTokens']);
Route::post('/addUserToken', [Web3Controller::class, 'addUserToken']);
Route::post('/manageToken', [Web3Controller::class, 'manageToken']);
Route::post('/removeToken', [Web3Controller::class, 'removeToken']);
Route::post('/awake', [Web3Controller::class, 'awake']);
