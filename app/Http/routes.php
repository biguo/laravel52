<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

//接口路由
$api = app('Dingo\Api\Routing\Router');

// 将所有的 Exception 全部交给 App\Exceptions\Handler 来处理
app('api.exception')->register(function (Exception $exception) {
    $request = Illuminate\Http\Request::capture();
    return app('App\Exceptions\Handler')->render($request, $exception);
});


$api->version('v1', ['namespace' => 'App\Api\Controllers'], function ($api) {

    $api->any('member/wxLogin', 'MemberController@wxLogin');//小程序用户登陆
    $api->any('member/wxRegister', 'MemberController@wxRegister');//小程序用户注册
    $api->any('member/getOpenid', 'MemberController@getOpenid');//小程序获取openid
    $api->any('member/minfo', 'MemberController@minfo');//

//    jwt 系列
    $api->post('user/login', 'AuthController@login');  //登录授权
    $api->post('user/register', 'AuthController@register');
    $api->post('user/logout', 'AuthController@logout');
    $api->post('user/refresh', 'AuthController@refresh');
    $api->post('user/me', 'AuthController@me');
    $api->post('user/refreshTest', 'AuthController@refreshTest');
    $api->post('user/useTest', 'AuthController@useTest');
});

