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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::get('robokassa/pay', 'PayController@robokassaPay');
Route::get('robokassa/renew', 'PayController@renew');
Route::post('robokassa/result',  'PayController@robokassaResult');
Route::get('get-status/{id}', 'PayController@getStatus');

Route::get('get-redis', 'WorkRedisController@getRedis');
Route::get('update-redis', 'WorkRedisController@updateRedis');
Route::get('delete-redis', 'WorkRedisController@deleteRedis');