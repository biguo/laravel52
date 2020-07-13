<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redis;
use Tools\Pay\Wechatpay;

class CopartnerApply extends Model
{
    protected $table = 'copartner_apply';
    protected $guarded = [];
    public $timestamps = false;

    public function member()
    {
        return $this->belongsTo(Member::class, 'mid');
    }
    /**
     * 根据tradeNo 获得订单   第二个参数表示状态条件 默认只获得未支付的, 0表示所有
     * @param null $trade_no
     * @param int $status
     * @return mixed
     */
    public static function getOrderByTradeNo($trade_no = null, $status = Status_UnPay)
    {
        if (!$trade_no) {
            $trade_no = Input::get('trade_no');
        }

        $where = [['trade_no', '=', $trade_no]];
        if ($status !== 0) {
            array_push($where, ["status", "=", $status]);
        }
        $order = self::where($where)->first();
        return $order;
    }

    /**
     *
     * @param $appid
     * @param $trade_no
     * @param $mid
     * @return \Illuminate\Http\JsonResponse
     */
    public static function doPay($mid, $trade_no)
    {
        $Order = self::getOrderByTradeNo($trade_no);
        if ($Order) { // 订单可以进行支付
            $appId = 'wxdfe1d168b25d4fff';

            $memberOauth = MemberOauth::getMemberOauthByMid($mid);
            if ((!$memberOauth)) {
                return responseError("未绑定openid");
            }

            $redisOpenid = Redis::get('country:openid:' . $mid);      //获得当前的用户在哪个村的小程序
            $openid = $redisOpenid ? $redisOpenid : $memberOauth->openid2;

            $weChatPay = new Wechatpay();
            $prepay_ver = $weChatPay->getXcxPrePayOrder($appId, '合伙人申请', $Order->trade_no.'_'.time(), $Order->price * 100, $openid, 'public/api/CoPartner/orderWxpaynotify');

            if (empty($prepay_ver) || !is_array($prepay_ver)) {
                return responseError('获取预支付订单失败');
            }

            $data['paydata'] = $weChatPay->getXcxOrder($appId, $prepay_ver['data']);
            //支付成功或添加账单明细
            return responseSuccess($data);
        } else {
            return responseError('订单不对,数据库无数据');
        }
    }


    /**
     *    {"appid":"wxd89dc01c5901c873",
     *     "bank_type":"CFT",
     *    "cash_fee":"1",
     *    "fee_type":"CNY",
     *    "is_subscribe":"N",
     *    "mch_id":"1487769092",
     *    "nonce_str":"iZhh3vtKc1KXIAWkmi8n6zVq4M3Ehri9",
     *    "openid":"ocaf_0YXGW2U1wdVWo2LQCGyOkow",
     *    "out_trade_no":"CoP2020071303026_1594627514",
     *    "result_code":"SUCCESS",
     *    "return_code":"SUCCESS",
     *    "sign":"F2DAE8D01E727D8F7BC263B89C9A8906",
     *    "time_end":"20180321163918",
     *    "total_fee":"1",
     *    "trade_type":"APP",
     *    "transaction_id":"4200000096201803212842821207"}
     */
    public function orderWxpaynotify($object)
    {
        $tradeNoArr= explode('_',$object->out_trade_no);
        $out_trade_no = $tradeNoArr[0];
        $order = self::getOrderByTradeNo($out_trade_no);  //调试完  把第二个参数去掉
        if ($order) {
            DB::beginTransaction();
            $order->status = Status_Payed;
            $order->paytradeno = $object->transaction_id;
            $order->responsestr = json_encode($object);
            $order->paytime = $object->time_end;
            $save = $order->save();
            $member = Member::getMemberByPhone($order->phone);
            if(!$member){
                Member::addNewMember(['phone' => $order->phone]);
                $member = Member::getMemberByPhone($order->phone);
            }
            $member->update(['type' => 3]);
            $member->increment('benefit', 10000);
            if ($save) {
                DB::commit();
                return responseSuccess("支付成功");
            } else {
                DB::rollback();
                return responseSuccess("支付失败");
            }
            return responseSuccess($data);
        }
        return responseError('订单不对,数据库无数据');

    }


}
