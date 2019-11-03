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
    //文件上传
    Route::post('/file/upload', 'Upload@upload')->name('upload.file');
    //获取项目详情
    Route::post('/getProjectDetails', 'Projects@getProjectDetails')->name('projects.getProjectDetails');
});




