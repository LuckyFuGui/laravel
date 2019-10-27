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
 * https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxb2b4c22f8c889787&redirect_uri=http%3a%2f%2fad.smalllucky.com%2foauth.php&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect
 * 前台接口
 */
Route::group(['namespace' => 'Api'], function(){
    Route::get('index', 'IndexController@index');
});
