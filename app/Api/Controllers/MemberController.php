<?php

namespace App\Api\Controllers;

use App\Models\AccountRecord;
use App\Models\Card;
use App\Models\CopartnerApply;
use App\Models\Country;
use App\Models\InviteCode;
use App\Models\Item;
use App\Models\Member;
use App\Models\MemberOauth;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Tools\SmsCode\SmsCode;

class MemberController extends BaseController
{

    /**
     * 我的个人信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function minfo(Request $request)
    {
        if ($request->isMethod('GET')) {
            $mid = $this->checkLogin($request);
            if (!$mid) {
                return responseError('请登录');
            }
            $member = Member::getMemberById($mid);
            $array = array();
            if ($member)
                $array = array_only($member->toarray(), ['id', 'phone', 'headpic', 'nickname', 'description', 'point']);

            $member->orders()->where('status', Status_UnPay)->delete();
            $cop = CopartnerApply::where([['phone','=', $member->phone],['status','=', Status_Payed]])->first();
            $array['current_image'] = $cop?  wanted : finding;

            $count = DB::table('order')->where('mid',$mid)
                ->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where('product_id', '=', '23')->whereIn('status', ['2', '6']);
                    })
                    ->orWhere([['product_id','=', '22'], ['status','=','2']])
                    ->orWhere([['product_id','=', '26'], ['status','=','2']]);
                })->count();

            $array['doingOrders'] = ($count >= 3)? 0 : 1;  # 还能不能再购入产品了
            $array['payed'] = $member->orders->where('status', Status_Payed)->count();
            $saved = Order::where('mid', $mid)->whereIn('status', [Status_Payed, Status_OrderUsed])->sum('saved');
            $array['saved'] = ($saved === null) ? 0 :$saved ;
            $array['history_number'] = AccountRecord::where('mid', $mid)->where('change', Change_Recharge)->count();
            return responseSuccess($array);
        }
        return responseError('非法请求');
    }

    /**
     * 合伙人基础信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function copartnerInfo(Request $request)
    {
        if ($request->isMethod('GET')) {
            $mid = $this->checkLogin($request);
            if (!$mid) {
                return responseError('请登录');
            }
            $user = Member::where('id',$mid)->select('totalamount', 'benefit', 'phone')->first();
            if(!$user)
                return responseError('找不到对象');
            $user = $user->toarray();
            $user['sums'] = CopartnerApply::where([['phone','=', $user['phone']], ['status','=', Status_Payed]])->sum('price');
            return responseSuccess($user);
        }
        return responseError('非法请求');
    }


    /**
     * 当前订单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function currentOrder(Request $request)
    {
        if ($request->isMethod('GET')) {
            $mid = $this->checkLogin($request);
            if (!$mid) {
                return responseError('请登录');
            }
            $orders = Order::where([['mid','=', $mid], ['status','=', Status_Payed]])->select('id', DB::raw('concat("'.Upload_Domain.'",unuse_image) as unuse_image'))->get();
            return responseSuccess($orders);
        }
        return responseError('非法请求');
    }

    /**
     * 个人卡券列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cards(Request $request)
    {
        if ($request->isMethod('GET')) {
            $mid = $this->checkLogin($request);
            if (!$mid) {
                return responseError('请登录');
            }
            $where = [['mid','=',$mid]];
            if($id = $request->get('id')){
                array_push($where, ['id','=', $id]);
            }
            $array = [
              '23' => 'http://upload.binghuozhijia.com/uploads/5eb4f17dea871/5eb4f17dea489.jpg',
              '22' => 'http://upload.binghuozhijia.com/uploads/5ec78496817ac/5ec784968175b.jpg',
              '26' => 'http://upload.binghuozhijia.com/uploads/5ec7849d2c765/5ec7849d2c71a.jpg',
            ];

            $orders = Order::where($where)->orderBy('created_at','desc')->select('trade_no', DB::raw("if(status=2,'0','1') as status"), DB::raw('concat("'.Upload_Domain.'",image) as image'), 'product_id')->get();
            foreach ($orders as $order){
                $item = Item::from('item as i')->leftJoin('product_item as pi','pi.item_id','=','i.id')->where('pi.product_id', $order->product_id)->select('title as info', 'description')->get()->toarray();
                $order->item = $item;
                if(isset($array[$order->product_id])){
                    $order->pic = $array[$order->product_id];
                }
            }
            return responseSuccess($orders);
        }
        return responseError('非法请求');
    }


    /**
     * 使用代金券
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function useVoucher(Request $request)
    {
        if ($request->isMethod('POST')) {
            $mid = $this->checkLogin($request);
            if (!$mid) {
                return responseError('请登录');
            }
            $data = $request->all();

            $order = Order::where(['trade_no' => $data['code'], 'status' => Status_Payed, 'mid' => $mid])->first();
            if (!$order)
                return responseError('没有符合条件的订单');
            $order->status = Status_OrderUsed;
            $order->save();
            return responseSuccess($data['code']);
        } else
            return responseError('非法请求');
    }

    /**
     * 充值记录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rechargeList(Request $request)
    {
        if ($request->isMethod('GET')) {
            $mid = $this->checkLogin($request);
            if (!$mid) {
                return responseError('请登录');
            }
            $object = AccountRecord::where('mid', $mid)->where('change', Change_Recharge)->orderBy('created_at','desc')->get();
            return responseSuccess($object);
        }
        return responseError('非法请求');
    }


    /**
     * 登录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function wxLogin(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = $request->all();
            if (!isset($data['uid']) || empty($data['uid'])) {
                return responseError('uid没传');
            }
            $member = Member::getMemberOautch(array('o.openid2' => $data['uid']));
            $country_id = Country::current()->id;

            $return['country_id'] = $country_id;
            $return['exist'] = 0;

            if ($member) {
                $return = $member->toarray();
                Redis::set('country:openid:' . $return['id'], $data['uid']);  //指定当前的用户在哪个村的小程序

                $return['jwttoken'] = $this->JwtEncryption($member);
                $return['exist'] = 1;
            }
            return responseSuccess($return);
        } else
            return responseError('非法请求');
    }

    /**
     * 绑定注册
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function wxRegister(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = $request->all();
            if (empty($data['uid']) || empty($data['phone']) || empty($data['vercode'])) {
                return responseError('必传字段为空');
            }
            //验证码验证
            $smscode = new SmsCode();
            $res = $smscode->checkSmsCode($data['phone'], $data['vercode'], SmsCodeType_REGISTER);
            if (IS_STRING($res))
                return responseError($res, $data);
            $member = Member::getMemberByPhone($data['phone']);

            if (!$member) {
                //新建用户
                DB::beginTransaction();
                $mid = Member::addNewMember($data);
                if (!$mid) {
                    DB::rollback(); //事务回滚
                    return responseError('注册失败', $data);
                }

                $oauth = MemberOauth::addMemberOauth($data['uid'], $mid);
                if ($oauth['report'] == 'fail') {
                    DB::rollback(); //事务回滚
                    return responseError($oauth['msg'], $data);
                }
                DB::commit();
            } else {
                $mid = $member->id;
                $memberAuth = MemberOauth::getXcxMemberOauth($data['uid'], $mid);
                if (!$memberAuth) {
                    MemberOauth::addMemberOauth($data['uid'], $mid);
                }
            }
            Redis::set('country:openid:' . $mid, $data['uid']);  //指定当前的用户在哪个村的小程序

            $minfo = Member::getMemberById($mid);
            $member = $minfo->toarray();
            $member['jwttoken'] = $this->JwtEncryption($minfo);
            return responseSuccess($member, '注册成功');
        } else
            return responseError('非法请求');
    }


    /**
     * 获得唯一标识openid
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOpenid(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = $request->all();
            if (empty($data['code'])) {
                return responseError('必传字段为空');
            }
            $country = Country::current();
            $curl = curl_init();
            //使用curl_setopt() 设置要获得url地址
            $url = "https://api.weixin.qq.com/sns/jscode2session?appid=$country->appid&secret=$country->appsecret&js_code=" . $data['code'] . '&grant_type=authorization_code';
            curl_setopt($curl, CURLOPT_URL, $url);
            //设置是否输出header
            curl_setopt($curl, CURLOPT_HEADER, false);
            //设置是否输出结果
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            //设置是否检查服务器端的证书
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            //使用curl_exec()将curl返回的结果转换成正常数据并保存到一个变量中
            $return = curl_exec($curl);
            //关闭会话
            curl_close($curl);
            $return = json_decode($return, true);
            return responseSuccess($return);
        }
        return responseError('非法请求');
    }

}
