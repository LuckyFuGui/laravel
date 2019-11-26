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
        // 查询单个
        Route::post('onlyIndex', 'AddressController@onlyIndex');
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

    //保存用户下单信息
    Route::post('/userOrdersSaves', 'Projects@userOrdersSaves')->name('projects.userOrdersSaves');

    //查询下单数据
    Route::post('/getOrdersInfo', 'Projects@getOrdersInfo')->name('projects.getOrdersInfo');

    //获取当前用户可用优惠券列表
    Route::post('/getDiscountByUser', 'DiscountUser@getDiscountByUser')->name('projects.getDiscountByUser');

    //当前发布的优惠券活动
    Route::post('/getDiscount', 'DiscountUser@getDiscount')->name('projects.getDiscount');

    //我的卡券
    Route::post('/userCoupon', 'DiscountUser@userCoupon')->name('projects.userCoupon');

    //个人中心
    Route::post('/userCenter', 'DiscountUser@userCenter')->name('projects.userCenter');

    //阿姨中心
    Route::post('/workerCenter', 'Worker@workerCenter')->name('Worker.workerCenter');

    //获取24小时内待完成订单
    Route::post('/workerOrders', 'Worker@workerOrders')->name('Worker.workerOrders');
});

