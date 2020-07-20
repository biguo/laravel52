<?php

namespace App\Api\Controllers;

use App\Models\CopartnerApply;
use App\Models\CopartnerRebate;
use App\Models\Member;
use Illuminate\Http\Request;

class CopartnerApplyController extends BaseController
{

    /**
     * 合伙人申请与支付
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apply(Request $request)
    {
        $mid = $this->checkLogin($request);
        if (!$mid) {
            return responseError('请登录');
        }
        if ($request->isMethod('POST')) {
            $all = $request->all();
            $where  = array_only($all, ['trade_no', 'id']);
            $attr = array_except($all, ['trade_no', 'id', 'piece']);
            $attr['mid'] = $mid;

            $basePrice = 1;
            $attr['price'] = $request->get('piece') * $basePrice;

            if(isset($all['trade_no']) && isset($all['id'])){
                $partner = CopartnerApply::where($where)->first();
                if($partner){
                    $partner->update($attr);
                    return CopartnerApply::doPay($mid, $partner->trade_no); // 支付成功
                }
            }
            $attr['trade_no'] = 'CoP' . StrOrderOne();
            $partner = CopartnerApply::create($attr);
            if (!$partner) {
                return responseError("订单提交失败!");
            }
            return CopartnerApply::doPay($mid, $partner->trade_no); // 支付成功
        } else {
            $partner = CopartnerApply::where([['mid','=',$mid], ['status','=',Status_UnPay]])->orderBy('id','desc')->select('phone', 'id', 'trade_no')->first();
            return responseSuccess($partner);
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
        return (new CopartnerApply())->orderWxpaynotify($response);
    }

    /**
     * 合伙人返利收益
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rebate(Request $request)
    {
        $mid = $this->checkLogin($request);
        if (!$mid) {
            return responseError('请登录');
        }
        if ($request->isMethod('POST')) {
            $data['res'] = CopartnerRebate::dataList($mid);
            $data['count'] = Member::where('pid' , $mid)->count();
            $data['totalamount'] = Member::where('id' , $mid)->first()->totalamount;
            return responseSuccess($data);
        }
    }
}
