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
    Route::post('banner/index', 'BannerController@index');
    // 服务项目查询
    Route::post('project/index', 'ProjectController@index');
    // 地址管理
	Route::group(['prefix'=>'address'], function(){
		// 注册
	    Route::post('store', 'AddressController@store');
	    // 查询
	    Route::post('index', 'AddressController@index');
	    // 删除
	    Route::post('destroy', 'AddressController@destroy');
	    // 修改
    	Route::post('save', 'AddressController@save');
	});
	// 订单管理
	Route::group(['prefix'=>'order'], function(){
		// 注册
	    Route::post('store', 'OrderController@store');
	    // 查询
	    Route::post('index', 'OrderController@index');
//	    // 删除
//	    Route::post('destroy', 'OrderController@destroy');
//	    // 修改
//    	Route::post('save', 'OrderController@save');
    	////////////////////////
    	Route::post('timedate', 'TimedateController@index');
        ////////////////////////
        Route::post('onlyIndex', 'OrderController@onlyIndex');
	});

	// 评论管理
	Route::group(['prefix'=>'comment'], function(){
		// 创建评论
	    Route::post('create', 'Comment@create');
	});
    //文件上传
    Route::post('/file/upload', 'Upload@upload')->name('upload.file');

    //获取项目详情
    Route::post('/getProjectDetails', 'Projects@getProjectDetails')->name('projects.getProjectDetails');

    //日常保洁/新居开荒服务数据提供
    Route::post('/getProjectData', 'Projects@getProjectData')->name('projects.getProjectData');

    //获取所有启用状态的枚举数据
    Route::post('/getDictionaryData', 'Projects@getDictionaryData')->name('projects.getDictionaryData');
});

