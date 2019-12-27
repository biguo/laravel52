<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable ;

class Member extends Authenticatable implements JWTSubject
{

    protected $table = 'member'; //指定表名
    protected $primaryKey = 'id'; //指定id
    public $timestamps = false;

    public function orders()
    {
        return $this->hasMany(Order::class, 'mid');
    }

    public function unPayOrders()
    {
        return $this->orders()->where('status', Status_UnPay)->get();
    }

    public function cards()
    {
        return $this->hasMany(Card::class, 'mid');
    }

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
        $data = array_except($data, ['vercode', 'pphone', 'uid']);
        $mid = DB::table('member')->insertGetId($data);
        $original = DB::connection('original')->table("member")->where('phone', $data['phone'])->first();  //同步此用户到app,但仅是同步手机号, 账户等信息不互通
        if(!$original){
            DB::connection('original')->table('member')->insert($data);
        }
        return $mid;
    }


    public static function getMemberById($mid)
    {
        return self::where('id', $mid)->first();
    }

    public static function getMemberByPhone($phone)
    {
        return self::where('phone', $phone)->first();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
