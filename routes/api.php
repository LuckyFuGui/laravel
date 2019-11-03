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
	// 用户授权
    Route::get('index', 'IndexController@index');
    // 轮播图查询
    Route::post('banner', 'BannerController@index');
    //文件上传
    Route::post('file/upload', 'Upload@upload')->name('upload.file');
});




