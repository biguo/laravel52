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


}
