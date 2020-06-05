<?php

namespace App\Api\Controllers;


class WeixinController extends BaseController   // 微信/小程序一系列接口 用于直播
{

    /**
     * 获得新增的临时素材
     */
    public function getMediaId()
    {
        $args['media'] = new \CurlFile(realpath('20200604154748.png'));  // 因为环境已经是php7.0之后
//        $args['media'] = new \CurlFile($_FILES['Filedata']['tmp_name']);
        $token = gettoken('wxdfe1d168b25d4fff');
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=" . $token .'&type=image';
        $curl = curl_init();//初始化
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 100);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $args);
        curl_exec($curl);//执行命令
        curl_close($curl);
    }

}
