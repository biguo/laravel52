<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redis;
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

    public function product()
    {
        return $this->belongsTo(Product::class);
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
        return self::where($where)->first();
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
     *
     * @param $appid
     * @param $trade_no
     * @param $mid
     * @return \Illuminate\Http\JsonResponse
     */
    public static function doPay($total, $mid)
    {
        $Order = Order::getOrderByTradeNo();
        if ($Order) { // 订单可以进行支付
            $appId = $Order->country()->first()->appid;
            if (sprintf("%.2f", $Order->price) !== sprintf("%.2f", $total)) {
                return responseError('最终价格不一样');
            }

            $memberOauth = MemberOauth::getMemberOauthByMid($mid);
            if ((!$memberOauth) || (!$memberOauth->openid)) {
                return responseError("未绑定openid");
            }

            $redisOpenid = Redis::get('country:openid:' . $mid);      //获得当前的用户在哪个村的小程序
            $openid = $redisOpenid ? $redisOpenid : $memberOauth->openid;

            $weChatPay = new Wechatpay();
            $prepay_ver = $weChatPay->getXcxPrePayOrder($appId, $Order->title, $Order->trade_no, $Order->price * 100, $openid, 'public/api/order/orderWxpaynotify');

            if (empty($prepay_ver) || !is_array($prepay_ver)) {
                return responseError('获取预支付订单失败');
            }

            $data['paydata'] = $weChatPay->getXcxOrder($appId, $prepay_ver['data']);
            $data['leftamount'] = Member::getMemberById($mid)->leftamount;
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
     *    "out_trade_no":"HOME2018032131226",
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

        $order = Order::getOrderByTradeNo($object->out_trade_no);  //调试完  把第二个参数去掉
        if ($order) {
            DB::beginTransaction();
            $order->status = Status_Payed;
            $order->paytradeno = $object->transaction_id;
            $order->responsestr = json_encode($object);
            $order->paytime = $object->time_end;
            $save = $order->save();
            $array = array_only(array_merge($order->toarray(), object_array($object)), ['trade_no', 'title', 'mid', 'total_fee', 'country_id']);
            $record = AccountRecord::create($array);
            $increment = Member::getMemberById($order->mid)->increment("leftamount", $order->price);
            $this->prepareCard($order);
            if ($save && $record && $increment) {
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

    public function prepareCard($order)
    {
        Card::where('mid', $order->mid)->delete();
        $orderArr = $order->toarray();
        $info = array_only($orderArr, ['mid', 'country_id']);

        $levelArray = [
            'type' => 'level',
            'category' => 'level',
            'info' => Upload_Domain . $order->image,
            'description' => $order->title,
            'status' => Status_Used,
            'code' => ''
        ];
        $CardTypeArray = [
            'single' => [
                'type' => 'single',
                'category' => 'discount',
                'info' => '9',
                'description' => '单间入住折扣券'
            ],
            'whole' => ['type' => 'whole',
                'category' => 'discount',
                'info' => '8.5',
                'description' => '整栋入住折扣券'
            ],
            'coffee' => ['type' => 'coffee',
                'category' => 'voucher',
                'info' => '咖啡代金券',
                'description' => '冯梦龙咖啡厅'
            ]
        ];
        foreach ($orderArr as $key => $value) {
            if (isset($CardTypeArray[$key]) && ($CardType = $CardTypeArray[$key]) && ($value > 0)) {
                for ($i = 1; $i <= $value; $i++) {
                    $info = array_merge($info, $CardType);
                    $info['code'] = 'USE' . StrOrderOne();
                    Card::create($info);
                }
            }
        }
        $info = array_merge($info, $levelArray);
        Card::create($info);
    }
}
