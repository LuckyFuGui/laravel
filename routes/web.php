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
	Route::group(['prefix'=>'admin'], function (){
		// 注册
	    Route::post('story', 'AdminController@story');
	    // 登陆
	    Route::post('login', 'AdminController@login');
	    // 退出
	    Route::post('outLogin', 'AdminController@outLogin');
	    // 删除
	    Route::post('destroy', 'AdminController@destroy');
	});
    // 轮播图
	Route::group(['prefix'=>'banner'], function(){
		// 注册
	    Route::post('store', 'BannerController@store');
	    // 查询
	    Route::post('index', 'BannerController@index');
	    // 删除
	    Route::post('destroy', 'BannerController@destroy');
	    // 修改
    	Route::post('save', 'BannerController@save');
	});

    Route::get('index', 'IndexController@index');
});
