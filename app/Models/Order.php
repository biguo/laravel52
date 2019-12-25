<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tools\Pay\Wechatpay;

class Order extends Model
{
    protected $table = 'order';
    protected $fillable = ['title', 'price', 'image', 'country_id', 'product_id', 'mid', 'trade_no', 'single', 'whole', 'coffee', 'wine', 'cake'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'mid');
    }

    /**
     * @param $all // 所需的参数
     */
    public function AddOrder($all)
    {
        $product = Product::getById();
        if ($product) {
            if (number_format($product->price, 2) != number_format($all['total'], 2)) {
                return responseError("计算金额不对");
            }
            $data = array_only($product->toarray(), ['title', 'price', 'image', 'country_id', 'single', 'whole', 'coffee', 'wine', 'cake']);
            $all = array_except($all, ['total']);
            $data = array_merge($data, $all);
            $data['trade_no'] = 'Add' . StrOrderOne();
            $flag = self::create($data);
            if ($flag) {
                return responseSuccess(["msg" => "订单提交成功", "trade_no" => $data['trade_no']]);
            } else {
                return responseError("订单提交失败!");
            }
        } else {
            return responseError("商品不存在!!");
        }
    }

    /**
     * @param $tradeno // 订单号
     * @return \Illuminate\Http\JsonResponse
     */
    public function doPay($appid,$trade_no, $mid)
    {

        $Order = DB::table("order")->where([['trade_no', '=', $trade_no], ['status', '=', 1]])->first();
        if ($Order) { // 订单可以进行支付

            $member_oauth = DB::table("member_oauth")->where(['mid' => $mid, 'oauth_type' => 4, 'delstatus' => DataDeleteStatus_NORMAL])->first();
            if ((!$member_oauth) || (!$member_oauth->openid2)) {
                return responseError("未绑定openid");
            }
            $wechatPay = new Wechatpay();
            $prepay_ver = $wechatPay->getXcxPrePayOrder($appid,$Order->title, $Order->tradeno, $Order->price * 100, $member_oauth->openid2, 'public/api/order/orderWxpaynotify');

            // return  responseError("sdvsdvdss",$prepay_ver);
            if (empty($prepay_ver) || !is_array($prepay_ver)) {
                return responseError('获取预支付订单失败');
            }

            $str = $wechatPay->getXcxOrder($appid, $prepay_ver['data']);
            //支付成功或添加账单明细
            return responseSuccess($str);

        } else {
            return responseError('订单不对,数据库无数据');
        }
    }

}
