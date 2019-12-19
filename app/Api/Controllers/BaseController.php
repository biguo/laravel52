<?php

namespace App\Api\Controllers;

use App\Http\Controllers\Controller;
use Tymon\JWTAuth\JWTAuth;
use Dingo\Api\Routing\Helpers;

class BaseController extends Controller
{
    use Helpers;

    public function __construct()
    {

    }

    //判断用户是否登陆
    public function checkLogin($request,$JWTAuth)
    {
        $jwttoken = $request->header("jwttoken");

        if($jwttoken!='' && !empty($jwttoken) && $jwttoken !=='[object Undefined]') {
            if ($minfo = $JWTAuth->toUser($jwttoken)) {
                return $minfo->toarray()["id"];
            }

        }
        return false;

    }
    //Jwt给用户加密
    public function JwtEncryption($data,JWTAuth $JWTAuth)
    {
        return $JWTAuth->fromUser($data);
    }
}