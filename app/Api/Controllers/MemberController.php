<?php

namespace App\Api\Controllers;

use App\Models\Country;
use App\Models\Member;
use App\Models\MemberOauth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Tools\SmsCode\SmsCode;

class MemberController extends BaseController
{

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
                $array = array_only($member->toarray(), ['id', 'phone', 'headpic', 'nickname']);
            return responseSuccess($array);
        }
        return responseError('非法请求');
    }

    public function wxLogin(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = $request->all();
            if (!isset($data['uid']) || empty($data['uid'])) {
                return responseError('uid没传');
            }
            $member = Member::getMemberOautch(array('o.openid' => $data['uid']));
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

            $minfo = Member::getMemberById($mid);
            $member = $minfo->toarray();
            $member['jwttoken'] = $this->JwtEncryption($minfo);
            return responseSuccess($member, '注册成功');
        } else
            return responseError('非法请求');
    }


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
