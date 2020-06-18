<?php

namespace App\Api\Controllers;

use App\Models\Member;
use App\Models\Streamer;
use App\Models\Video;
use App\Models\VideoLike;
use Illuminate\Http\Request;

class WeixinController extends BaseController   // 微信/小程序一系列接口 用于直播
{

    public function applyStreamer(Request $request)
    {
        if ($request->isMethod('POST')) {

            $mid = $this->checkLogin($request);
            if (!$mid) {
                return responseError('请登录');
            }
            $member = Member::find($mid);
            if (!$member) {
                return responseError('请注册绑定');
            }
            $streamer = Streamer::where('mid',$mid)->whereIn('status', [Status_Online_streamer, Status_Real_streamer])->first();
            if($streamer){
                return responseError("您已经通过了");
            }
            $names = ['realname', 'nickname'];
            $member->update($request->only($names));
            $all = $request->except($names);
            $all['mid'] = $mid;
            Streamer::create($all);
            return responseSuccessArr('创建成功');
        } else {
            return responseError("不是post请求!!");
        }
    }

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

    /**
     *  创建直播室
     *
     * {
     * "name": "测试直播间", //房间名字 最长17个汉字，1个汉字相当于2个字符
     * "coverImg": "xxxxxx", //填写mediaID，直播间背景图，图片规则：建议像素1080*1920，大小不超过2M，mediaID获取参考：https://developers.weixin.qq.com/doc/offiaccount/Asset_Management/New_temporary_materials.html）
     * "startTime": 1588237130, // 直播计划开始时间，1.开播时间需在当前时间10min后，2.开始时间不能在6个月后
     * "endTime": 1588237130,  //直播计划结束时间，1.开播时间和结束时间间隔不得短于30min，不得超过12小时
     * "anchorName": "test1", // 主播昵称 最长15个汉字，1个汉字相当于2个字符
     * "anchorWechat":"test1", //主播微信号，需通过实名认证，否则将报错
     * "shareImg":"xxx", //填写mediaID，直播间分享图，图片规则：建议像素800*640，大小不超过1M，mediaID获取参考：https://developers.weixin.qq.com/doc/offiaccount/Asset_Management/New_temporary_materials.html）
     * "type":1, //直播类型，1：推流，0：手机直播
     * "screenType":0, //1：横屏，0：竖屏，自动根据实际视频分辨率调整
     * "closeLike":0, //1：关闭点赞 0：开启点赞 ，关闭后无法开启
     * "closeGoods":0, //1：关闭货架 0：打开货架，关闭后无法开启
     * "closeComment":0 //1：关闭评论 0：打开评论，关闭后无法开启
     * }
     */
    public function CreateLiveRoom()
    {
        $appid = 'wxdfe1d168b25d4fff';
        $data = array(
            "name" => "测试直播间", //房间名字 最长17个汉字，1个汉字相当于2个字符
            "coverImg" => "5qYAeZfO5bn9u_jOx4c7qL3ald9hsZrpOoQyjgBomk6YtLYrSQo-p-exF1QT2Rcr", //填写mediaID，直播间背景图，图片规则：建议像素1080*1920，大小不超过2M，mediaID获取参考：https://developers.weixin.qq.com/doc/offiaccount/Asset_Management/New_temporary_materials.html）
            "startTime" => 1592182800, // 直播计划开始时间，1.开播时间需在当前时间10min后，2.开始时间不能在6个月后
            "endTime" => 1592186400,  //直播计划结束时间，1.开播时间和结束时间间隔不得短于30min，不得超过12小时
            "anchorName" => "test1", // 主播昵称 最长15个汉字，1个汉字相当于2个字符
            "anchorWechat" => "SZ624136772", //主播微信号，需通过实名认证，否则将报错
            "shareImg" => "t1xhRHMVMIv-2BSaMWdH8p_0EaQn-HGlulJRQpXcKoez5guTxr_N92JF5I1Q1KUl", //填写mediaID，直播间分享图，图片规则：建议像素800*640，大小不超过1M，mediaID获取参考：https://developers.weixin.qq.com/doc/offiaccount/Asset_Management/New_temporary_materials.html）
            "type" => 0, //直播类型，1：推流，0：手机直播
            "screenType" => 0, //1：横屏，0：竖屏，自动根据实际视频分辨率调整
            "closeLike" => 1, //1：关闭点赞 0：开启点赞 ，关闭后无法开启
            "closeGoods" => 1, //1：关闭货架 0：打开货架，关闭后无法开启
            "closeComment" => 1 //1：关闭评论 0：打开评论，关闭后无法开启
        );
        $interface = 'https://api.weixin.qq.com/wxaapi/broadcast/room/create';
        $token = gettoken($appid);
        $url = $interface . "?access_token=" . $token;
        $json_data = JSON($data);
        $ret = doCurlPostRequest($url, $json_data, 'json');
        print_r($ret);
    }

    /**
     *  获取直播房间列表
     */
    public function getLiveInfo()
    {
        $appid = 'wxdfe1d168b25d4fff';
        $data = array(
            "start" => 0,
            "limit" => 10
        );
        $interface = "http://api.weixin.qq.com/wxa/business/getliveinfo";
        $token = gettoken($appid);
        $url = $interface . "?access_token=" . $token;
        $json_data = JSON($data);
        $ret = doCurlPostRequest($url, $json_data);
        print_r($ret);
    }

    /**
     * 获取回放源视频
     */
    public function getReplay()
    {
        $appid = 'wxdfe1d168b25d4fff';
        $data = array(
            "action" => 'get_replay',
            "room_id" => 2,
            "start" => 0,
            "limit" => 10
        );
        $interface = "http://api.weixin.qq.com/wxa/business/getliveinfo";
        $token = gettoken($appid);
        $url = $interface . "?access_token=" . $token;
        $json_data = JSON($data);
        $ret = doCurlPostRequest($url, $json_data);
        print_r($ret);
    }

    /**
     * 上传视频接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function UploadVideo(Request $request)
    {
        if ($request->isMethod('POST')) {

            $mid = $this->checkLogin($request);
            if (!$mid) {
                return responseError('请登录');
            }
            $all = $request->all();
            if(isset($all['tags'])){
                $tagArr = json_decode($all['tags'],true);
                $all['tags'] = implode(',',$tagArr);
            }
            $all['mid'] = $mid;
            $all['project'] = '乡村民宿';
            $res = Video::create($all);
            $like['source_id'] = $res->id;
            $like['mid'] = $mid;
            VideoLike::create($like);
            return responseSuccessArr('创建成功');
        } else {
            return responseError("不是post请求!!");
        }
    }

    /**
     * 视频列表
     * @param Request $request
     * @return array
     */
    public function VideoList(Request $request)
    {
        $mid = $this->checkLogin($request);
        $res = (new Video())->VideoPublishedList($mid);
        return responseSuccessArr($res);
    }

    /**
     * 滑动加载视频列表
     * @param Request $request
     * @return array
     */
    public function VideoSlippingList(Request $request)
    {
        $mid = $this->checkLogin($request);
        $res = (new Video())->VideoPopularityList($mid, $request->get('source_id'));
        return responseSuccessArr($res);
    }


    /**
     * 点赞/取消点赞视频
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function LikeVideo(Request $request)
    {
        $mid = $this->checkLogin($request);
        $like['source_id'] = $request->get('source_id');
        $like['mid'] = $mid;
        $like['type'] = 1;
        $first = VideoLike::where($like)->first();
        if($first){
            $first->delete();
            return responseSuccess('取消成功');
        }else{
            VideoLike::create($like);
            return responseSuccess('点赞成功');
        }
    }

    /**
     * 获得标签
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTags()
    {
        $tagArr = [[
            'title' => '行在旅途',
            'pic' => Upload_Domain . 'uploads/5ee6cb73a2ccf/5ee6cb73a2ccc.jpg',
            'tags' => [
                '呼吸自然',
                '夏日避暑地',
                '在美丽乡村当村民吧',
                '旅途中的最美夜景',
                '值得去的古镇乡村',
                '拍照超美的打卡地',
                '我的旅行vlog'
            ]],
            [
                'title' => '美食推荐',
                'pic' => Upload_Domain . 'uploads/5ee6cb77cf5f4/5ee6cb77cf5f2.jpg',
                'tags' => [
                    '自然风味美食',
                    '我的私家食堂',
                    '当地才能吃到的美食',
                    '家乡小吃我来pick',
                    '浪漫约会餐',
                    '最爱下午茶时光',
                    '这个酒吧有点燃'
                ]],
            [
                'title' => '精彩民宿',
                'pic' => Upload_Domain . 'uploads/5ee6cb7c871f3/5ee6cb7c871f1.jpg',
                'tags' => [
                    '轰趴必去的民宿啊',
                    '少女心爆棚的民宿',
                    '乡村里的民宿',
                    '这些民宿风景真赞',
                    '和萌娃一起的亲子民宿',
                    '夏日度假避暑首选',
                    '性价比超高的民宿'
                ]]
        ];
        return responseSuccess($tagArr);
    }


}
