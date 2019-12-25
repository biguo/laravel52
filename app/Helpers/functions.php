<?php

//api接口返回成功
function responseSuccess($data = array(), $message = '操作成功', $code = '200')
{
    return response()->json(['report' => 'ok', 'code' => $code, 'data' => $data, 'msg' => $message, 'action' => 'ACTION_NONE']);
}

function responseError($message = '操作失败', $data = array(), $code = '500')
{
    return response()->json(['report' => 'fail', 'code' => $code, 'data' => $data, 'msg' => $message, 'action' => 'ACTION_NONE']);
}

function responseSuccessArr($data = array(), $message = '操作成功', $code = '200')
{
    return ['report' => 'ok', 'code' => $code, 'data' => $data, 'msg' => $message, 'action' => 'ACTION_NONE'];
}

function responseErrorArr($message = '操作失败', $data = array(), $code = '500')
{
    return ['report' => 'fail', 'code' => $code, 'data' => $data, 'msg' => $message, 'action' => 'ACTION_NONE'];
}

function test_function()
{
    return 'test_function';
}

//生成唯一订单号
function StrOrderOne(){
    return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}
/**
 *  这个比上面那个好用
 */
function doCurlPostRequest($url, $requestString, $headertype = '', $timeout = 10) {
    if ($url == "" || $requestString == "" || $timeout <= 0) {
        return false;
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
//    curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
    if (!empty($requestString)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $requestString);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    if ('xml' == $headertype) {
        $header[] = "Content-Type: text/xml";
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    }
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

/**
 * 封装获取token的调用接口
 * @param type $force
 * @return boolean
 */
function gettoken($appid, $force = false) {

    $redis = \Illuminate\Support\Facades\Redis::connection('default');
    $cachename = 'wx_accesstoken:'.$appid;
    $token = $redis->get($cachename);
    if ($force || empty($token)) {
        //微信接口取accesstoken
        $para = array(
            "grant_type" => "client_credential",
            "appid" => $appid,
            "secret" => env('SECRET')
        );
        $url = "https://api.weixin.qq.com/cgi-bin/token";
        $url = $url . '?' . http_build_query($para);
        $ret = file_get_contents($url);
        $retData = json_decode($ret, true);
        if (!$retData) {
            return false;
        }
        $token = $retData['access_token'];
        $expire = $retData['expires_in'];
        //保存到缓存
        $redis->setex($cachename,$expire, $token);
    }
    return $token;
}

/**
 * post 微信
 * @param type $interface
 * @param type $data
 * @return boolean
 */
function postWeixinInterface($interface, $data, $appid) {
    $token = gettoken($appid);
    $url = $interface . "?access_token=" . $token;
    $json_data = JSON($data);
    $ret = doCurlPostRequest($url, $json_data);
    return $ret;
}

/**
 * 解决json_encode中文问题和其它字符
 * @param type $array
 * @return type
 */
function JSON($array) {
    arrayRecursive($array, 'urlencode', true);
    $json = json_encode($array);
    return urldecode($json);
}

/**
 * 数组数据字符操作函数
 * @staticvar int $recursive_counter
 * @param type $array
 * @param type $function
 * @param type $apply_to_keys_also
 */
function arrayRecursive(&$array, $function1, $apply_to_keys_also = false) {
    static $recursive_counter = 0;
    if (++$recursive_counter > 1000) {
        die('possible deep recursion attack');
    }
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            arrayRecursive($array[$key], $function1, $apply_to_keys_also);
        } else {
            $array[$key] = $function1($value);
        }

        if ($apply_to_keys_also && is_string($key)) {
            $new_key = $function1($key);
            if ($new_key != $key) {
                $array[$new_key] = $array[$key];
                unset($array[$key]);
            }
        }
    }
    $recursive_counter--;
}