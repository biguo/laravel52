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
    $api->any('member/minfo', 'MemberController@minfo');// 我的信息

    $api->any('common/getSmsCode', 'CommonController@getSmsCode'); //验证码
    $api->any('common/shareXcx', 'CommonController@shareXcx');  // 分享二维码
    $api->any('common/img', 'CommonController@uploadImg');  //上传图片(七牛

});

