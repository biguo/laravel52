<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Member extends Model
{

    protected $table = 'member'; //指定表名
    protected $primaryKey = 'id'; //指定id
    public $timestamps = false;


    //根据用户第三方登陆信息获取用户信息
    public static function getMemberOautch($where)
    {
        return self::from('member as m')->where($where)
            ->join('member_oauth as o', 'm.id', '=', 'o.mid')
            ->select('m.*')->first();
    }

    //新建用户
    public static function addNewMember($data)
    {
        $data['nickname'] = isset($data['nickname']) ? $data['nickname'] : substr($data['phone'], 0, 3) . '****' . substr($data['phone'], strlen($data['phone']) - 4, 4);
        $data['headpic'] = isset($data['headpic']) ? $data['headpic'] : Default_Pic;
        $data['regtime'] = date('Y-m-d H:i:s', time());
        $data['regip'] = $_SERVER['SERVER_ADDR'];
        $data['pid'] = (isset($data['pphone']) && trim($data['pphone']) != '' && self::where('phone', $data['pphone'])->value('id')) ? self::where('phone', $data['pphone'])->value('id') : 0;

        $data = array_except($data, ['vercode', 'pphone', 'uid']);

        return DB::table('member')->insertGetId($data);
    }


    public static function getMemberById($mid)
    {
        return Member::where('id', $mid)->first();
    }

    public static function getMemberByPhone($phone)
    {
        return Member::where('phone', $phone)->first();
    }

    //修改用户信息
    public static function updateMemberInfo($data)
    {
        $mid = intval(@$data['id']);
        $upfield = array(
            'phone',
            'nickname',
            'idcardno',
            'headpic',
            'verstatus',
            'cardfront',
            'cardback'
        );
        $update = array();
        foreach ($data as $k => $v) {
            if (in_array($k, $upfield)) {
                $update[$k] = $v;
            }
        }
        return DB::table('member')->where('id', $mid)->update($update);
    }

}
