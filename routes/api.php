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

/**
 * 前台接口
 */
Route::group(['namespace' => 'Api'], function(){
    Route::get('index', 'IndexController@index');
});
/**
 * 测试
 */
Route::group(['namespace' => 'Api'], function(){
    Route::get('test', 'IndexController@test');
});
