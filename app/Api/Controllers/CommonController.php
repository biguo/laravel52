<?php

namespace App\Api\Controllers;

use App\Models\FileModel;
use App\Models\Member;
use Illuminate\Support\Facades\Redis;
use Tools\SmsCode\SmsCode;
use Validator;
use Illuminate\Http\Request;
use DB;

class CommonController extends BaseController
{

    //发送短信验证码
    public function getSmsCode(Request $request)
    {
        if ($request->getMethod('POST')) {
            $phone = $request->input('phone');
            if (empty($phone)) {
                return responseError('请求超时');
            }
            //验证
            $validator = Validator::make($request->all(), ['phone' => 'required', 'type' => 'required'], ['required' => ':attribute必填'], ['phone' => '手机号', 'type' => '验证码类型']);
            if ($validator->fails()) {
                return responseError(current($validator->errors()->messages())[0]);//自行封装个处理验证失败返回值 类似下面
            }
            if (!preg_match("/^1[3456789]\d{9}$/", $phone)) {
                return responseError('手机号格式不正确');
            }
            $data = $request->all();
            if ($data['type'] == 1)//1-注册 手机号存在，报错
            {
                if (Member::where('phone', $phone)->count() > 0) {
                    return responseError('当前手机号已注册');
                }
            }
            //发送验证码
            $smscode = new SmsCode();
            return $smscode->getSmsCode($phone, $data['type']);
        } else {
            return responseError('登陆方式有误');
        }
    }


    public function shareXcx(Request $request)
    {
        $pphone = $this->request->input('pphone');
        if (!$pphone) {
            return responseError('请输入推荐手机号');
        }
        $slug = $request->input('slug');
        if (!$slug) {
            return responseError('请输入小程序店主手机号');
        }
        $redis = Redis::connection('default');
        $appid = DB::table('country')->where('slug', $slug)->value('appid');
        $cachename = 'api_scene:pphone:' . $appid . ':' . $pphone;
        $url = $redis->get($cachename);
        if (!$url) {
            $interface = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit';
            $Data = array('scene' => 'pphone=' . $pphone);
            $dd = postWeixinInterface($interface, $Data, $appid);
            $url = (new FileModel())->uploadStream($dd, 'erwm.png');
            if (!$url) {
                return responseError('上传图片失败');
            }
            $redis->set($cachename, $url);
        }
        return responseSuccess($url);
    }

    public function uploadImg()
    {
        $tmpFile = @$_FILES['file']['tmp_name'];
        $qiniu = new FileModel();

        $newName = uniqid() . '.jpg';
        $result = $qiniu->uploads($tmpFile, $newName);
        return response()->json([['error' => 0, 'url' => $result]]);
    }

    // 获取城市 -- 二级联动
    public function  getCityAndArea(Request $request){
        $pid = $request->get('id');
        return responseSuccess(DB::connection('original')->table("districts")->where("pid","=",$pid)->select("id","name as text")->get());
    }
}
