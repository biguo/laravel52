<?php

namespace App\Api\Controllers;

use App\Models\Country;
use App\Models\Member;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends BaseController
{
    public function getList(Request $request)
    {
        return responseError('非法请求');
    }

    /**
     * 下单
     * @return \Illuminate\Http\JsonResponse
     */
    public function AddOrder(Request $request)
    {

        $mid = $this->checkLogin($request);
        if (!$mid) {
            return responseError('请登录');
        }
        // 接受全部的参数
        $inputs = $request->all();
        if (!isset($inputs['product_id'])) {
            return responseError('请输入产品id');
        }
        $member = Member::getMemberById($mid);
        if (count($member->unPayOrders()) > 0) {
            return responseError('有未支付的订单');
        }
        $inputs['mid'] = $mid;
        return (new Order())->AddOrder($inputs);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function doPay(Request $request)
    {
        if ($request->isMethod('POST')) {

            $mid = $this->checkLogin($request);
            if (!$mid) {
                return responseError('请登录');
            }
            $all = $request->all(); // 获取所有的传参
            if (!isset($all['tradeno'])) { // 订单号是否为空
                return responseError("订单号为空!!!");
            }
            if(!isset($all['total'])){
                return responseError('请输入总价');
            }
            $all['mid'] = $mid;
            //验证订单是否可以支付
            $order = Order::where('tradeno',$all['tradeno'])->first();
            // 检查用户的价格是否正确
            $price = $order->checkPrice($all['total'], $all['tradeno']);
//            $price = DB::table("order")->where([["tradeno", "=", $tradeno]])->value('price');
            if (!$price['flag']) {
                return responseError($price['msg']);
            }
            $appid = DB::table('shop')->where('id', $order->shop_id)->value('appid');
            // 进行订单的支付 -- 没有问题进行支付
            $result = $order->dopay($appid, $all['tradeno'], $all['mid']); // 支付成功
            return $result;
//            return responseSuccess();
        } else {
            return responseError("不是post请求!!");
        }

    }


}
