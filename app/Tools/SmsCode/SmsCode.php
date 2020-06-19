<?php
//融云云之讯
namespace Tools\SmsCode;


use App\Models\Vercode;
use DB;

class SmsCode
{
    //云之讯
    private $accountsid = '7a31085f7e4e8814088ea27740b4cb1d';
    private $token = 'f624d253fbfb0ec18a191fa019b489c3';
    //private $appId = 'aabd636307644a14a1698e0d9e849dce';//正式
    private $appId = 'bdce0260b3804dd28c2213968a86a80f';//测试
    //云之讯模板
    private $yzx_tmp_reg = '379937';//注册
    private $yzx_tmp_login = '179908';//登陆

    private $timelimit = 1; // 发送短信时间间隔（分钟）

    //获取验证码 phone-手机号 type-验证码类型 vctype- 1-云之讯
    public function getSmsCode($phone, $type)
    {
        if (empty($phone)) {
            return responseErrorArr('请输入手机号');
        }

        // 短信发送频率验证
        if (Vercode::where(['phone' => $phone, 'type' => $type])->whereBetween('createtime', array(date('Y-m-d H:i:s', time() - intval($this->timelimit) * 60), date('Y-m-d H:i:s', time())))->count() > 0) {
            return responseErrorArr('发送短信间隔太短，请稍后再来');
        }

        $code_data['phone'] = $phone;
        $code_data['type'] = $type;
        // 云之讯

        $code = sprintf('%04d', mt_rand(0, 9999));
        $code_data['code'] = $code;
        $rlt = DB::table('vercode')->insert($code_data);
        if ($rlt) {
            // 发送短信验证码
            $cnt = 0;
            while ($cnt < 3 && ($ret = $this->SendYunZYMsg($phone, $code, $type)) === FALSE){
                $cnt++;
            }
            if ($ret !== '0') {
                return responseSuccessArr($ret);
            } else {
                return responseErrorArr('获取验证码失败:');
            }
        }
        return responseErrorArr('获取验证码失败');


    }


    //检测验证码 $phone 手机号 $code 验证码 $type 验证码类型
    public function checkSmsCode($phone, $code, $type)
    {
        $timeout = 15;
        $where['phone'] = $phone;
        $where['type'] = $type;
        $createtime = array(date('Y-m-d H:i:s', time() - intval($timeout) * 60), date('Y-m-d H:i:s', time()));
        $where['code'] = $code;
        if (Vercode::where($where)->whereBetween('createtime', $createtime)->count() <= 0) {
            responseErrorArr('验证码已过期或不正确');
            return '验证码已过期或不正确';
        }
        return true;
    }

    /**
     * SendYunZYMsg
     * 云之讯发送短信接口
     *
     * @access public
     * @param mixed $phone 手机号
     * @param mixed $code 验证码
     * @param mixed $type 验证类型 1-注册 2-重设登录密码 3-重设支付密码
     * @since 1.0
     * @return array
     */
    public function SendYunZYMsg($phone, $code, $type)
    {
        /*初始化*/
        $options['accountsid'] = $this->accountsid;
        $options['token'] = $this->token;
        $appId = $this->appId;
        switch ($type) {
            case SmsCodeType_LOGIN://登陆
                $templateId = $this->yzx_tmp_login;
                break;
            case SmsCodeType_REGISTER://注册
                $templateId = $this->yzx_tmp_reg;
                break;
        }
        $param = $code;
        $ucpass = new Ucpaas($options);
        $ret = $ucpass->templateSMS($appId, $phone, $templateId, $param);
        $ret = json_decode($ret, TRUE);
        $respCode = $ret['resp']["respCode"];
        if($respCode == '000000')
            return $code;
        else
            return 0;
    }

    #云之讯
    public function SendYunmsg($phone, $templateId, $param = null)
    {
        $options['accountsid'] = $this->accountsid;
        $options['token'] = $this->token;
        $appId = $this->appId;
        $ucpass = new Ucpaas($options);
        $ret = $ucpass->templateSMS($appId, $phone, $templateId, $param);
        $ret = json_decode($ret, TRUE);
        return $ret;
    }

}