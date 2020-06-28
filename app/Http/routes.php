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
    $api->any('member/cards', 'MemberController@cards');// 我的卡券
    $api->any('member/useVoucher', 'MemberController@useVoucher');// 使用代金券
    $api->any('member/rechargeList', 'MemberController@rechargeList');// 充值记录

    $api->any('common/getSmsCode', 'CommonController@getSmsCode'); //验证码
    $api->any('common/shareXcx', 'CommonController@shareXcx');  // 分享二维码
    $api->any('common/img', 'CommonController@uploadImg');  //上传图片(七牛
    $api->any('common/testSMS', 'CommonController@testSMS');  //测试短信

    $api->any('banner/list', 'BannerController@getList');  //轮播图

    $api->any('product/orderShowBefore', 'ProductController@orderShowBefore');  //下单前页面
    $api->any('product/getList', 'ProductController@getList');  //会员卡列表

    $api->post('order/addOrder', 'OrderController@addOrder');  //下单
    $api->post('order/doPay', 'OrderController@doPay');  //付款
    $api->any('order/orderWxpaynotify', 'OrderController@orderWxpaynotify');//订单回调地址
    $api->any('order/testOrder', 'OrderController@testOrder');//调试order
    $api->post('order/Refund', 'OrderController@Refund');  //退款

    $api->get('getCityAndArea','CommonController@getCityAndArea'); //省市区

    $api->any('wx/applyStreamer','WeixinController@applyStreamer'); // 申请直播者资质
    $api->any('wx/getMediaId','WeixinController@getMediaId'); //上传媒介到小程序后台 获得新增的临时素材
    $api->any('wx/CreateLiveRoom','WeixinController@CreateLiveRoom'); //【创建直播间】接口
    $api->any('wx/getLiveInfo','WeixinController@getLiveInfo'); //【获取直播房间列表】接口
    $api->any('wx/getLiveRoom','WeixinController@getLiveRoom'); // 获取直播房间列表
    $api->any('wx/LiveRoomDetail','WeixinController@LiveRoomDetail'); // 获取直播房间详情
    $api->any('wx/getReplay','WeixinController@getReplay'); //【获取回放源视频】接口

    $api->any('wx/UploadVideo','WeixinController@UploadVideo'); // 上传视频
    $api->any('wx/VideoList','WeixinController@VideoList'); // 视频列表
    $api->any('wx/VideoSlippingList','WeixinController@VideoSlippingList'); // 滑动加载视频列表
    $api->any('wx/LikeVideo','WeixinController@LikeVideo'); // 点赞/取消视频
    $api->any('wx/getTags','WeixinController@getTags'); // 视频标签
    $api->any('wx/pass','WeixinController@pass'); // 视频标签

});

