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
    $router->any('changeStatus', 'ExampleController@changeStatus');
});
