<?php

namespace App\Api\Controllers;

use App\Models\Country;
use App\Models\Member;
use App\Models\MemberOauth;
use App\Models\Order;
use Illuminate\Http\Request;
use Tools\Pay\Wechatpay;

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
     * 付款
     * @return \Illuminate\Http\JsonResponse
     */
    public function doPay(Request $request)
    {
        if ($request->isMethod('POST')) {

            $mid = $this->checkLogin($request);
            if (!$mid) {
                return responseError('请登录');
            }
            $all = $request->all();
            if (!isset($all['trade_no'])) {
                return responseError("订单号为空!!!");
            }
            if (!isset($all['total'])) {
                return responseError('请输入总价');
            }

            return Order::doPay($all['total'], $mid); // 支付成功
        } else {
            return responseError("不是post请求!!");
        }

    }

    /**
     * 退款
     */
    public function Refund(Request $request)
    {
        if($request->isMethod('POST')) {
            $trade_no = $request->input('trade_no');
            $order = Order::getOrderByTradeNo($trade_no,0);
            if(!$trade_no){
                return responseError('订单号未传' .$trade_no);
            }
            if($order->status === Status_Payed){
                $code =  (new Wechatpay())->xcxRefundWechat($order->country->appid,$trade_no, $order->price * 100, $order->price*100);
                if($code === 200){
                    $order->status = Status_Refund;
                    $order->save();
                    return responseSuccessArr('退款成功');
                }else{
                    return responseErrorArr('退款失败');
                }
            }
        }
    }


    /**
     * 订单的Wxpay的回调地址
     * @return \Illuminate\Http\JsonResponse
     *    {"appid":"wxd89dc01c5901c873",
     *     "bank_type":"CFT",
     *    "cash_fee":"1",
     *    "fee_type":"CNY",
     *    "is_subscribe":"N",
     *    "mch_id":"1487769092",
     *    "nonce_str":"iZhh3vtKc1KXIAWkmi8n6zVq4M3Ehri9",
     *    "openid":"ocaf_0YXGW2U1wdVWo2LQCGyOkow",
     *    "out_trade_no":"HOME2018032131226",
     *    "result_code":"SUCCESS",
     *    "return_code":"SUCCESS",
     *    "sign":"F2DAE8D01E727D8F7BC263B89C9A8906",
     *    "time_end":"20180321163918",
     *    "total_fee":"1",
     *    "trade_type":"APP",
     *    "transaction_id":"4200000096201803212842821207"}
     */
    public function orderWxpaynotify()
    {
        $response = simplexml_load_string(file_get_contents("php://input"), 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($response === false) {
            return responseError('parse xml error！');
        }
        if ($response->return_code != 'SUCCESS') {
            return responseError('支付失败(' . $response->err_code . '):' . $response->return_msg);
        }
        return (new Order())->orderWxpaynotify($response);
    }


}
