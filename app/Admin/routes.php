<?php

use Illuminate\Routing\Router;

Admin::registerHelpersRoutes();

Route::group([
    'prefix'        => config('admin.prefix'),
    'namespace'     => Admin::controllerNamespace(),
    'middleware'    => ['web', 'admin'],
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('banner', BannerController::class);
    $router->resource('country', CountryController::class);
    $router->get('/orders', 'OrderController@index');
    $router->resource('product', ProductController::class);
//    $router->resource('house', HouseController::class);
    $router->resource('item', ItemController::class);
    $router->resource('rule', RuleController::class);
    $router->resource('video', VideoController::class);
    $router->any('streamer/{id}/show', 'StreamerController@show');  //查看
    $router->resource('streamer', StreamerController::class);
    $router->any('LiveApply/{id}/show', 'LiveApplyController@show');  //查看
    $router->any('streamerPass', 'LiveApplyController@pass'); //主播实名
    $router->any('toLive', 'LiveApplyController@toLive'); //直播房间开通
    $router->resource('LiveApply', LiveApplyController::class);
    $router->resource('Copartner', CopartnerApplyController::class);
    $router->any('changeStatus', 'ExampleController@changeStatus');
});
