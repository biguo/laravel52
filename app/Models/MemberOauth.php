<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberOauth extends Model
{

    protected $table = 'member_oauth';

    public $timestamps = false;


    public static function addMemberOauth($data, $mid)
    {
        $data = ['mid' => $mid, 'openid' => $data['uid']];
        //判断登陆关系是否已存在
        $num = self::where($data)->count();
        if ($num > 0)
            return responseErrorArr('注册失败，请联系客服');
        $res = self::insert($data);
        if ($res) {
            return responseSuccessArr('成功');
        } else {
            return responseErrorArr('注册失败');
        }
    }

    public static function getXcxMemberOauth($data, $mid)
    {
        return self::where(['mid' => $mid, 'openid' => $data['uid']])->first();
    }

}
