<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//测试文件上传
// Route::get('/file/upload', 'Test@fileUpload');

/**
 * 分组
 * 后台接口
 */
Route::group(['namespace' => 'Web', 'prefix' => 'web'], function(){
	// 登陆
    Route::post('admin/create', 'AdminController@create');
    Route::post('admin/login', 'AdminController@login');
    Route::post('admin/outLogin', 'AdminController@outLogin');
    // 轮播图
    Route::post('banner/create', 'BannerController@create');
    Route::post('banner/destroy', 'BannerController@destroy');
    Route::post('banner/show', 'BannerController@show');

    Route::get('index', 'IndexController@index');
});
