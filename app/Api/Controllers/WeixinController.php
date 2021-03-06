<?php

namespace App\Api\Controllers;

use App\Models\FileModel;
use App\Models\Follow;
use App\Models\LiveApply;
use App\Models\Member;
use App\Models\Streamer;
use App\Models\Video;
use App\Models\VideoLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redis;
use Tools\SmsCode\SmsCode;

class WeixinController extends BaseController   // 微信/小程序一系列接口 用于直播
{

    /**
     * 申请获得直播资质
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
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
            $streamer = Streamer::where('mid', $mid)->whereIn('status', [Status_Online_streamer, Status_Real_streamer])->first();
            if ($streamer) {
                return responseError("您已经通过了");
            }
            if (!$request->get('nickname') || strlen($request->get('nickname')) < 4 || strlen($request->get('nickname')) > 30) {
                return responseError('请提供合适长度(4-30字节)的昵称');
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
    public function putMedia($source)
    {
        $img = base64_decode($source);
        $new_path = base_path('public') . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR . microtime(true) * 10000 . '.png';
        @file_put_contents($new_path, $img);
        return $new_path;
    }

    public function getMediaId($new_path)
    {
        $args['media'] = new \CurlFile($new_path);
        $token = gettoken('wxdfe1d168b25d4fff', true);
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=" . $token . '&type=image';
        $curl = curl_init();//初始化
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 100);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $args);
        $output = curl_exec($curl);
        curl_close($curl);
        unlink($new_path);
        return $output;
    }

    /**
     * 图片安全内容检测接口
     */
    public function checkMedia($new_path)
    {
        $args['media'] = new \CurlFile($new_path);
        $token = gettoken('wxdfe1d168b25d4fff', true);
        $url = "https://api.weixin.qq.com/wxa/img_sec_check?access_token=" . $token ;
        $curl = curl_init();//初始化
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 100);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $args);
        $output = curl_exec($curl);
        curl_close($curl);
        $arr = json_decode($output, true);
        return $arr;
    }

    /**
     * 文字安全内容检测接口
     */
    public function checkContent($content)
    {
        $data = array(
            "content" => $content
        );
        $interface = "https://api.weixin.qq.com/wxa/msg_sec_check";
        $token = gettoken('wxdfe1d168b25d4fff',true);
        $url = $interface . "?access_token=" . $token;
        $json_data = JSON($data);
        $ret = doCurlPostRequest($url, $json_data);
        $arr = json_decode($ret, true);
        return $arr;
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
    public function CreateLiveRoom(Request $request)
    {
        if ($request->isMethod('POST')) {
            $params = $request->all();
            $mid = $this->checkLogin($request);
            if (!$mid) {
                return responseError('请登录');
            }
            $member = Member::find($mid);
            if (!$member) {
                return responseError('请注册绑定');
            }
            $streamer = Streamer::where('mid', $mid)->whereIn('status', [Status_Online_streamer, Status_Real_streamer])->first();
            if (!$streamer) {
                return responseError('无此主播或主播申请还未通过');
            }

            if (!isset($params['title']) || strlen($params['title']) < 6 || strlen($params['title']) >34) {
                return responseError('请提供合适长度(6-34字节)的房间名称');
            }
            $startTime = strtotime($params['Date'] . '' . $params['startTime']);
            $endTime = strtotime($params['Date'] . '' . $params['endTime']);
            if ($startTime > $endTime) {
                return responseError('开始时间须小于结束时间');
            }
            $newPath = $this->putMedia($params['coverImg']);

            $arr0 = $this->checkMedia($newPath);
            if($arr0['errcode'] !== 0){
                unlink($newPath);
                return responseError("没有通过图片检验!!");
            }

            $arr1 = $this->checkContent($params['title']);
            if($arr1['errcode'] !== 0){
                return responseError("没有通过文字检验!!");
            }

            $coverImg = (new FileModel())->uploads($newPath, uniqid() . '.jpg');
            $res = $this->getMediaId($newPath);
            $arr = json_decode($res, true);
            $data = array(
                "name" => $params['title'], //房间名字 最长17个汉字，1个汉字相当于2个字符
                "coverImg" => $coverImg, //填写mediaID，直播间背景图，图片规则：建议像素1080*1920，大小不超过2M，mediaID获取参考：https://developers.weixin.qq.com/doc/offiaccount/Asset_Management/New_temporary_materials.html）
                "startTime" => $startTime,// 直播计划开始时间，1.开播时间需在当前时间10min后，2.开始时间不能在6个月后
                "endTime" => $endTime,  //直播计划结束时间，1.开播时间和结束时间间隔不得短于30min，不得超过12小时
                "mid" => $mid, // 主播昵称 最长15个汉字，1个汉字相当于2个字符
                "streamer_id" => $streamer->id, //主播微信号，需通过实名认证，否则将报错
                "shareImg" => $coverImg, //填写mediaID，直播间分享图，图片规则：建议像素800*640，大小不超过1M，mediaID获取参考：https://developers.weixin.qq.com/doc/offiaccount/Asset_Management/New_temporary_materials.html）
                "type" => 0, //直播类型，1：推流，0：手机直播
                "screenType" => 0, //1：横屏，0：竖屏，自动根据实际视频分辨率调整
                "closeLike" => 0, //1：关闭点赞 0：开启点赞 ，关闭后无法开启
                "closeGoods" => 1, //1：关闭货架 0：打开货架，关闭后无法开启
                "closeComment" => 0, //1：关闭评论 0：打开评论，关闭后无法开启
                "coverMedia" => $arr['media_id'], //
                "shareMedia" => $arr['media_id'], //
            );
            if(isset($params['city'])){
                $data['city'] = $params['city'];
            }
            LiveApply::create($data);
            return responseSuccessArr('创建成功');
        } else {
            return responseError("不是post请求!!");
        }
    }

    /**
     *  获取直播房间列表
     */
    public function getLiveInfo()
    {
        $data = array(
            "start" => 0,
            "limit" => 10
        );
        $interface = "https://api.weixin.qq.com/wxa/business/getliveinfo";
        $token = gettoken('wxdfe1d168b25d4fff',true);
        $url = $interface . "?access_token=" . $token;
        $json_data = JSON($data);
        $ret = doCurlPostRequest($url, $json_data);
        $arr = json_decode($ret, true);
        return $arr;
    }

    /**
     * 检查播放时间 重置播放状态
     */
    private function checkStage()
    {
//        $sql = "update live_apply set stage=(IF((unix_timestamp() > endTime),1,IF((unix_timestamp() < startTime),2,3))) where status=1";
//        DB::update($sql);
//        101：直播中，102：未开始，103已结束，104禁播，105：暂停，106：异常，107：已过期
//        0 未直播 1 已结束 2 未开始  3 已开始
        $statusArr = [
            '101' => '3',
            '102' => '2',
            '103' => '1',
            '104' => '1',
            '105' => '1',
            '106' => '1',
            '107' => '1'
        ];
        $Rooms = LiveApply::from('live_apply as a')->where('status', 1)->orderBy('stage', 'desc')->get();
        $arr = $this->getLiveInfo();
        if (($arr['errcode'] === 0) && (($arr['total'] >= 1))) {
            $room_info = $arr['room_info'];
            foreach ($Rooms as $item){
                foreach ($room_info as $v){
                    if($item->roomId === $v['roomid']){
                        $item->stage = $statusArr[$v['live_status']];
                        $item->save();
                        if($v['live_status'] === 103){
                            if ($item->live_replay === null) {
                                $this->getReplay($item->roomId, $item);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     *  获取直播房间列表
     */
    public function getLiveRoom(Request $request)
    {
        $this->checkStage();
        $mid = $this->checkLogin($request);
        $pageStatus = 0;  # 是否进入过此页面 0 未 1 已  未登录默认未进入
        $liveStatus = 0;  # 当事人申请直播的状态 0 没有瓜葛 1 已上线(未实名)  2 审核中 3已下线 4 已驳回 5 已实名
        $liveStr = '未申请';
        if ($mid) {
            $redis = Redis::connection('default');
            $cacheName = 'api_live:' . $mid;
            if (!$redis->exists($cacheName)) {
                $redis->set($cacheName, 1);
            } else {
                $pageStatus = 1;
            }

            if (Streamer::where('mid', $mid)->where('status', 5)->first()) {
                $liveStatus = 5;
                $liveStr = '已实名';
            } elseif (Streamer::where('mid', $mid)->where('status', 1)->first()) {
                $liveStatus = 1;
                $liveStr = '已通过';
            } elseif (Streamer::where('mid', $mid)->first()) {
                $liveStatus = 2;
            } else {
                $liveStatus = 0;
            }
        }
        $data['pageStatus'] = $pageStatus;
        $data['liveStatus'] = $liveStatus;
        $data['liveStr'] = $liveStr;
        $data['roomPic'] = 'http://upload.binghuozhijia.com/uploads/5f30e0ac37f6c/5f30e0ac37f6a.jpg';
        $order_type = $request->get('order_type') ;
        $order_type = ($order_type === '2' )? 2 :1;

        $where = [['status','=','1']];
        if(($city = $request->get('city')) &&($city !== null)){
            array_push($where, ['city','=',$city]);
        }
        $Query = LiveApply::from('live_apply as a')
            ->Leftjoin('iceland.ice_member as m', 'm.id', '=', 'a.mid')
            ->select('a.id','a.roomId','a.name', 'a.stage', 'a.status', 'a.mid', 'a.streamer_id', 'a.coverImg', 'a.shareImg', 'a.startTime', 'a.endTime','a.city', 'm.nickname as anchor_name', 'm.headpic')
            ->where($where);
        if($order_type === 2){
            $Rooms = $Query->orderBy('id', 'desc')->get();
        }else{
            $Rooms = $Query->orderBy('stage', 'desc')->get();
        }

        $stagePicArr = [
            '3' => ['pic' => 'http://upload.binghuozhijia.com/uploads/5ef7f017e96fd/5ef7f017e96fb.jpg', 'str' => '直播中'],
            '2' => ['pic' => 'http://upload.binghuozhijia.com/uploads/5ef7eff15bbe8/5ef7eff15bbe5.jpg', 'str' => '即将开始'],
            '1' => ['pic' => 'http://upload.binghuozhijia.com/uploads/5ef7efc82343c/5ef7efc82343a.jpg', 'str' => '已结束'],
            '0' => ['pic' => '', 'str' => '未通过'],
        ];
        $statusArr = [
            '1' => '已通过',
            '2' => '申请中',
            '4' => '驳回',
        ];
        foreach ($Rooms as $room) {
            $room->stagePic = $stagePicArr[$room->stage]['pic'];
            $room->stageStr = $stagePicArr[$room->stage]['str'];
            $room->statusStr = $statusArr[$room->status];
            $room->ImUpload = ($room->mid === $mid);
        }
        $data['Rooms'] = $Rooms;
        return responseSuccess($data);
    }

    /**
     * 直播室详细页
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function LiveRoomDetail(Request $request)
    {
        $id = $request->get('id');
        $mid = $this->checkLogin($request);

        $LiveApply = LiveApply::where('id', $id)->first();

        $select =  ['name', 'stage', 'coverImg', 'startTime', 'endTime', 'roomId', 'city'];
        if ($LiveApply->stage === 1) {
            if ($LiveApply->live_replay === null) {
                $this->getReplay($LiveApply->roomId, $LiveApply);
            }
            $select = array_merge($select, ['live_replay', 'live_count']);
        } elseif ($LiveApply->stage === 3) {
            $LiveApply->live_count += 1;
            $LiveApply->save();
        }

        $Room = array_only($LiveApply->toarray(), $select);
        $Room['range'] = date("Y-m-d H:i:s", $LiveApply->startTime) . '至' . date("Y-m-d H:i:s", $LiveApply->endTime);

        $Room['mid'] = $LiveApply->mid;
        $Room['follow'] = 0;
        if($mid){
            $follow = Follow::where([['followed','=',$LiveApply->mid],['mid','=',$mid]])->count();
            if($follow > 0){
                $Room['follow'] = 1;
            }
        }

        $stagePicArr = [
            '3' => ['pic' => 'http://upload.binghuozhijia.com/uploads/5ef7f017e96fd/5ef7f017e96fb.jpg', 'str' => '直播中'],
            '2' => ['pic' => 'http://upload.binghuozhijia.com/uploads/5ef7eff15bbe8/5ef7eff15bbe5.jpg', 'str' => '即将开始'],
            '1' => ['pic' => 'http://upload.binghuozhijia.com/uploads/5ef7efc82343c/5ef7efc82343a.jpg', 'str' => '已结束'],
            '0' => ['pic' => '', 'str' => '未通过'],
        ];
        $Room['stageStr'] = $stagePicArr[$LiveApply->stage]['str'];

        $data['info'] = $Room;

        $Streamer = Streamer::from('streamer as s')
            ->Leftjoin('iceland.ice_member as m', 'm.id', '=', 's.mid')
            ->select('m.nickname', 's.introduce')
            ->where('s.id', $LiveApply->streamer_id)
            ->first();
        $data['uploader'] = $Streamer;

        return responseSuccess($data);
    }

    /**
     * 获取回放源视频
     */
    public function getReplay($room_id, $LiveApply)
    {
        $data = array(
            "action" => 'get_replay',
            "room_id" => $room_id,
            "start" => 0,
            "limit" => 10
        );
        $interface = "https://api.weixin.qq.com/wxa/business/getliveinfo";
        $token = gettoken('wxdfe1d168b25d4fff');
        $url = $interface . "?access_token=" . $token;
        $json_data = JSON($data);
        $ret = doCurlPostRequest($url, $json_data);

        $arr = json_decode($ret, true);
        if (($arr['errcode'] === 0) && ($arr['total'] >= 1) && ($arr['live_replay']) && ($arr['live_replay'][1])) {
            $LiveApply->live_replay = $arr['live_replay'][1]['media_url'];
            $LiveApply->save();
        }
    }

    /**
     * 主播实名确认
     * @return \Illuminate\Http\JsonResponse
     */
    public function pass(Request $request)
    {
        $mid = $this->checkLogin($request);
        $Streamer = Streamer::where('mid', $mid)->first();
        $Streamer->status = 5;
        $Streamer->save();
        return responseSuccess();
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

            $img = file_get_contents('http://upload.binghuozhijia.com/'. $all['pic']);
            $new_path = base_path('public') . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR . microtime(true) * 10000 . '.png';
            @file_put_contents($new_path, $img);
            $arr0 = $this->checkMedia($new_path);
            unlink($new_path);
            if($arr0['errcode'] !== 0){
                return responseError("没有通过图片检验!!");
            }

            $arr1 = $this->checkContent($all['title']);
            if($arr1['errcode'] !== 0){
                return responseError("没有通过文字检验!!");
            }
            if (isset($all['tags'])) {
                $tagArr = json_decode($all['tags'], true);
                $all['tags'] = implode(',', $tagArr);
            }
            $all['mid'] = $mid;
            $all['project'] = '乡村民宿';
            Video::create($all);
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
        $order_type = $request->get('order_type') ;
        $order_type = ($order_type === '2' )? 2 :1;
        $res = (new Video())->VideoPublishedList($mid, $order_type, $request->get('city'),$request->get('paginate'));
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
        $res = (new Video())->VideoPopularityList($mid, $request->get('source_id'), $request->get('city'),$request->get('paginate'));
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
        if ($first) {
            $first->delete();
            return responseSuccess('取消成功');
        } else {
            VideoLike::create($like);
            return responseSuccess('点赞成功');
        }
    }

    /**
     * 关注/取消关注
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function follow(Request $request)
    {
        $mid = $this->checkLogin($request);
        $like['followed'] = $request->get('followed');
        $like['mid'] = $mid;
        $first = Follow::where($like)->first();
        if ($first) {
            $first->delete();
            return responseSuccess(['res' => 0, 'msg' => '取消关注']);
        } else {
            Follow::create($like);
            return responseSuccess(['res' => 1, 'msg' => '关注成功']);
        }
    }

    /**
     * 关注视频/直播列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function followList(Request $request)
    {
        $mid = $this->checkLogin($request);
        $data = DB::table('follow_list_view')->where('mid', $mid)->get();
        $stagePicArr = [
            '3' => ['pic' => 'http://upload.binghuozhijia.com/uploads/5ef7f017e96fd/5ef7f017e96fb.jpg', 'str' => '直播中'],
            '2' => ['pic' => 'http://upload.binghuozhijia.com/uploads/5ef7eff15bbe8/5ef7eff15bbe5.jpg', 'str' => '即将开始'],
            '1' => ['pic' => 'http://upload.binghuozhijia.com/uploads/5ef7efc82343c/5ef7efc82343a.jpg', 'str' => '已结束'],
            '0' => ['pic' => '', 'str' => '未通过'],
        ];
        foreach ($data as $item){
            $item->interval = $item->interval.'天前';
            if($item->source_type === 2){
                $item->like = 0;
                $item->like_count = VideoLike::where([['source_id','=',$item->id],['mid','=',$mid]])->count();
                if($mid){
                    $like = VideoLike::where([['source_id','=',$item->id],['mid','=',$mid]])->count();
                    if($like > 0){
                        $item->like = 1;
                    }
                }
            }else{
                $item->stagePic = $stagePicArr[$item->stage]['pic'];
                $item->stageStr = $stagePicArr[$item->stage]['str'];
                $item->ImUpload = ($item->followed === $mid);
            }
        }
        return responseSuccess($data);
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
