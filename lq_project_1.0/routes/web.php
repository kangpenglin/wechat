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

Route::get('/', function () {
    return view('welcome');
});

/*
 - 以下定义功能路由
*/

// 登陆路由
Route::post('autho/login', 'LqControl@login');


// 更新自己的信息
Route::post('me/{wechat_id}', 'LqControl@updateOWnDetails');


// 发布新活动
Route::post('activity', 'ActivityController@createActivity');

// 删除活动
Route::delete('activity/{wechat_id}/created/{activity_id}', 'ActivityController@deleteActivity');

// 更新活动
Route::post('activity/{wechat_id}/created/{activity_id}', 'ActivityController@updateActivity');


// 查询所有活动
Route::get('activity/{wechat_id}', 'ActivityController@getAllActivity');

// 搜索活动
Route::get('activity/search/{search_key}', 'ActivityController@searchActivity');

// 查看自己所在活动
Route::get('activity/list/{wechat_id}', 'ActivityController@getOwnActivity');

// 查看活动详情
Route::get('activity/{wechat_id}/detail/{activity_id}', 'ActivityController@getActivityDetails');

// 加入活动
Route::post('activity/detail/', 'ActivityController@participateActivity');

// 退出活动
Route::delete('activity/{wechat_id}/detail/{activity_id}', 'ActivityController@unparticipateActivity');

// 查看自己的信息
Route::get('me/{wechat_id}', 'LqControl@getOwnDetails');


Route::post('activity/shift', 'ActivityController@shiftActivity');

