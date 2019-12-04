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

Route::any('/',function (){
    return view('welcome');
});
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
	    // 列表
	    Route::post('index', 'AdminController@index');
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
	// 服务项目
	Route::group(['prefix'=>'project'], function(){
		// 注册
	    Route::post('store', 'ProjectController@store');
	    // 查询
	    Route::post('index', 'ProjectController@index');
	    // 删除
	    Route::post('destroy', 'ProjectController@destroy');
	    // 修改
    	Route::post('save', 'ProjectController@save');
	});
	// 地址管理
	Route::group(['prefix'=>'address'], function(){
	    // 查询
	    Route::post('index', 'AddressController@index');
	    // 删除
	    Route::post('destroy', 'AddressController@destroy');
	    // 修改
    	Route::post('save', 'AddressController@save');
	});

    // 员工管理
    Route::group(['prefix'=>'worker'], function(){
        // 列表
        Route::post('index', 'Worker@index')->name('worker.index');
        // 查看
        Route::post('cat', 'Worker@cat')->name('worker.cat');
        // 创建用户
        Route::post('create', 'Worker@create')->name('worker.create');
        // 手机号查询用户id
        Route::post('getUserBYTel', 'Worker@getUserBYTel')->name('worker.getUserBYTel');
        // 修改
        Route::post('update', 'Worker@update')->name('worker.update');
        // 员工状态变更
        Route::post('updateStatus', 'Worker@updateStatus')->name('worker.updateStatus');
        // 请假
        Route::post('leave', 'Worker@leave')->name('worker.leave');
    });

    // 项目管理-日常保洁/新居开荒
    Route::group(['prefix'=>'projects'], function(){
        // 编辑主服务价格
        Route::post('mainEdit', 'Projects@mainEdit')->name('projects.mainEdit');

        // 新居开荒基础数据
        Route::post('wasteland', 'Projects@wasteland')->name('projects.wasteland');

        // 日常保洁基础数据
        Route::post('daliy', 'Projects@daliy')->name('projects.daliy');

        // 附加项目编辑
        Route::post('servicesEdit', 'Projects@servicesEdit')->name('projects.servicesEdit');

        // 新增附加服务
        Route::post('servicesAdd', 'Projects@servicesAdd')->name('projects.servicesAdd');

        // 获取单个项目详情
        Route::post('getProjectDetails', 'Projects@getProjectDetails')->name('projects.getProjectDetails');

        // 附加服务上下架
        Route::post('serviceFrames', 'Projects@serviceFrames')->name('projects.serviceFrames');

        // 新增图片
        Route::post('addImg', 'Projects@addImg')->name('projects.addImg');

        // 删除图片
        Route::post('delImg', 'Projects@delImg')->name('projects.delImg');

        // 项目管理
        Route::get('index', 'Projects@index')->name('projects.index');

    });

    // 考勤管理
    Route::group(['prefix'=>'leave'], function(){
        // 列表
        Route::post('leaveManagement', 'Worker@leaveManagement')->name('worker.leave_management');

        // 取消请假
        Route::post('leaveCancel', 'Worker@leaveCancel')->name('worker.leave_cancel');
    });

    // 用户管理
    Route::group(['prefix'=>'user'], function(){
        // 列表
        Route::post('index', 'User@index')->name('user.index');

        // 取消请假
        Route::post('updateStatus', 'User@updateStatus')->name('user.update_status');
    });

    // 优惠活动管理
    Route::group(['prefix'=>'discount'], function(){
        // 列表
        Route::post('index', 'DiscountActivity@index')->name('discount_activity.index');

        // 新增优惠卷
        Route::post('add', 'DiscountActivity@add')->name('discount_activity.add');

        // 编辑代金劵活动
        Route::post('edit', 'DiscountActivity@edit')->name('discount_activity.edit');

        // 取消优惠卷
        Route::post('cancel', 'DiscountActivity@cancel')->name('discount_activity.cancel');
    });

    // 评论管理
    Route::group(['prefix'=>'comment'], function(){
        // 列表
        Route::post('index', 'Comment@index')->name('comment.index');
    });
    // 订单
    Route::group(['prefix'=>'order'], function(){
        // 列表
        Route::post('index', 'OrderController@index');
    });


    Route::get('index', 'IndexController@index');
    // 后台首页（日数据）
    Route::post('show', 'IndexController@show');
    // 月数据
    Route::post('show_month', 'IndexController@showMonth');


});
