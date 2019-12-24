<?php
namespace Tools\Pay;

use Illuminate\Support\Facades\DB;

class Wechatpay
{

   //配置参数
    private $config = array(
        'mch_id' => "1561941081",   /*微信申请成功之后邮件中的商户id*/
        'api_key' => "7hC9lS39QNVQXH4wxuSXvvhdslcSL3su",    /*在微信商户平台上自己设定的api密钥 32位*/
    );

    /*
        生成签名
    */
    function getSign($Obj,$flag)
    {
        foreach ($Obj as $k => $v)
        {
            $Parameters[strtolower($k)] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".$this->config['api_key'];
        //签名步骤三：MD5加密
        $result_ = strtoupper(md5($String));
        return $result_;
    }

    //获取指定长度的随机字符串
    function getRandChar($length = 32){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;

        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }

    //数组转xml
    function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val))
            {
                $xml.="<".$key.">".$val."</".$key.">";
            }
            else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }

    //post https请求，CURLOPT_POSTFIELDS xml格式
    function postXmlCurl($xml,$url,$second=30)
    {
        //初始化curl
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data)
        {
            curl_close($ch);
            return $data;
        }
        else
        {
            $error = curl_errno($ch);
            //echo "curl出错，错误码:$error"."<br>";
            //echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);
            return false;
        }
    }

    /*
        获取当前服务器的IP
    */
    public function get_client_ip()
    {
        if ($_SERVER['REMOTE_ADDR']) {
        	$cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
        	$cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
        	$cip = getenv("HTTP_CLIENT_IP");
        } else {
        	$cip = "unknown";
        }
        return $cip;
    }

    //将数组转成uri字符串
    function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            $buff .= strtolower($k) . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }
    
    public function getXcxPrePayOrder($appid, $body, $out_trade_no, $total_fee, $openid, $notify_url='public/api/Homeorder/homeorderWxpaynotify'){
        if(empty($body)){
            return responseErrorArr('未知产品名称');
        }
        if(empty($out_trade_no)){
            return responseErrorArr('未知订单号');
        }
        if(empty($openid)){
            return responseErrorArr('openid有误');
        }
        if(empty($total_fee)){
            return responseErrorArr('未知支付金额');
        }
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";

        $notify_url = getenv('APP_URL').$notify_url;//回调URL

        $onoce_str = $this->getRandChar(32);

        $data["appid"] = $appid;
        $data["body"] = $body;
        $data["mch_id"] = $this->config['mch_id'];
        $data["nonce_str"] = $onoce_str;
        $data["notify_url"] = $notify_url;
        $data["out_trade_no"] = $out_trade_no;
        $data["spbill_create_ip"] = $this->get_client_ip();
        $data["total_fee"] = $total_fee;
        $data["trade_type"] = "JSAPI";
        $data["openid"] = $openid;
        $s = $this->getSign($data, false);
        $data["sign"] = $s;
        $xml = $this->arrayToXml($data);
        $response = $this->postXmlCurl($xml, $url);
        $unifiedOrder = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false) {
            return responseErrorArr('转换XML出错');
        }
        $node = 'prepay_id';
        $result = $unifiedOrder->xpath($node);
        if($result){
            $nodeArr = (array)$result[0];
            $res = $nodeArr[0];
            return responseSuccessArr(''.$res.'');
        }
        return responseErrorArr('获取预支付订单失败');
    }
    
    public function getXcxOrder($appid,$prepayId){
        $data["appId"] = $appid;
        $nonceStr = $this->getRandChar(32);
        $data["nonceStr"] = $nonceStr;
        $data["package"] = "prepay_id=".$prepayId;
        $data["signType"] = "MD5";
        $time = Time();
        $data["timeStamp"] = ''.$time.'';
//        $s = $this->getSign($data, false);
//        $data["sign"] = $s;
//        $time = Time();
        $str = 'appId='.$appid.'&nonceStr='.$nonceStr.'&package=prepay_id='.$prepayId.'&signType=MD5&timeStamp='.$time;
        //重新生成签名
        $data['sign']=strtoupper(md5($str.'&key='.$this->config['api_key']));
        return $data;
    }

    public function xcxRefundWechat($appid,$out_trade_no,$amount, $total)  //$total是订单的总金额  $amount是这次退款要退多少 用于押金退款这种多次退款的类型
    {
        $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";//微信退款地址，post请求
        $onoce_str = $this->getRandChar(32);
        $out_refund_no = $this->getRandChar(32);
        $data["appid"] = $appid;
        $data["mch_id"] = $this->config['mch_id'];
        $data["nonce_str"] = $onoce_str;
        $data["op_user_id"] = $this->config['mch_id'];
        $data["out_trade_no"] = $out_trade_no; //商户内部唯一退款单号
        $data["out_refund_no"] = $out_refund_no;  //商户订单号,pay_sn码 1.1二选一,微信生成的订单号，在支付通知中有返回
        $data["refund_fee"] = $amount;
        $data["total_fee"] = $total;
        $s = $this->getSign($data, false);
        $data["sign"] = $s;
        $xml = $this->sourceXml($data);
        $response = $this->postXmlCurlWithPem($xml, $url);
        $news = preg_replace('/^([^\<]*)\</i', '<', $response); // 去除xml之前的部分
        $unifiedOrder = simplexml_load_string($news, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false) {
            return 500;
        }
        if($unifiedOrder->result_code == 'SUCCESS'){
            return 200;
        }else{
            return 500;
        }
    }

    //post https请求
    function postXmlCurlWithPem($xml,$url)
    {
//        $root = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;  #linux
//        $certPath = $root. 'public'.DIRECTORY_SEPARATOR.'wxcert'.DIRECTORY_SEPARATOR.'apiclient_cert.pem';
//        $keyPath = $root. 'public'.DIRECTORY_SEPARATOR.'wxcert'.DIRECTORY_SEPARATOR.'apiclient_key.pem';
        $certPath = getcwd().'\wxcert\apiclient_cert.pem';  #windows
        $keyPath = getcwd().'\wxcert\apiclient_key.pem';
        //初始化curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);//证书检查
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'pem');
        curl_setopt($ch, CURLOPT_SSLCERT, $certPath);
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'pem');
        curl_setopt($ch, CURLOPT_SSLKEY, $keyPath);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);
        if ($data) { //返回来的是xml格式需要转换成数组再提取值，用来做更新
            curl_close($ch);
            return $data;
        } else {
//            $error = curl_errno($ch);
//            echo "curl出错，错误代码：$error" . "<br/>";
//            echo "<a href='http://curl.haxx.se/libcurl/c/libcurs.html'>;错误原因查询</a><br/>";
            curl_close($ch);
            return false;
        }
    }

    public function sourceXml($arr)
    {
        $xml = "<root>";
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $xml .= "<" . $key . ">" . $this->arrayToXml($val) . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        $xml .= "</root>";
        return $xml;
    }
}