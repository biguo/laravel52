<?php

namespace App\Api\Controllers;

use App\Http\Controllers\Controller;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth as jwt;
use Dingo\Api\Routing\Helpers;

class BaseController extends Controller
{
    use Helpers;

    protected $JWTAuth;

    public function __construct(JWTAuth $JWTAuth)
    {
        $this->JWTAuth = $JWTAuth;
    }

    //判断用户是否登陆
    public function checkLogin($request, $field  = 'id')
    {
        $jwttoken = $request->header("jwttoken");

        if($jwttoken!='' && !empty($jwttoken) && $jwttoken !=='[object Undefined]') {
            jwt ::setToken($jwttoken);       //加这一行 这是JWT 0.5和1.0间的区别
            $user = $this->JWTAuth->toUser();
            if(( $user != '')&&($array = $user->toarray())){
                if(isset($field)){
                    return $array[$field];
                }
                return $array;
            }

        }
        return false;

    }
    //Jwt给用户加密
    public function JwtEncryption($data)
    {
        return $this->JWTAuth->fromUser($data);
    }
}