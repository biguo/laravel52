<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberOauth extends Model
{

    protected $table = 'member_oauth';
    protected $connection = 'original';
    public $timestamps = false;


    public static function addMemberOauth($openid, $mid)
    {
        $data = ['mid' => $mid, 'openid2' => $openid];
        //判断登陆关系是否已存在
        $num = self::where($data)->count();
        if ($num > 0)
            return responseErrorArr('注册失败，请联系客服');
        $data['project'] = '乡村民宿';
        $res = self::insert($data);
        if ($res) {
            return responseSuccessArr('成功');
        } else {
            return responseErrorArr('注册失败');
        }
    }

    public static function getXcxMemberOauth($openid, $mid)
    {
        return self::where(['mid' => $mid, 'openid2' => $openid])->first();
    }

    public static function getMemberOauthByMid($mid)
    {
        return self::where('mid', $mid)->first();
    }

}
