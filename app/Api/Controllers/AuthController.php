<?php

namespace App\Api\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Blacklist;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends BaseController
{

    /**
     * Create a new AuthController instance.
     * 要求附带email和password（数据来源users表）
     * @return void
     */
    public function __construct()
    {
        // 这里额外注意了：官方文档样例中只除外了『login』
        // 这样的结果是，token 只能在有效期以内进行刷新，过期无法刷新
        // 如果把 refresh 也放进去，token 即使过期但仍在刷新期以内也可刷新
        // 不过刷新一次作废
        $this->middleware('jwt.auth', ['except' => ['login', 'register']]);
        $this->middleware('refreshtoken', ['only' => ['refreshTest']]);
        // 另外关于上面的中间件，官方文档写的是『auth:api』
        // 但是我推荐用 『jwt.auth』，效果是一样的，但是有更加丰富的报错信息返回
    }


    /**
     * 用户使用邮箱密码获取JWT Token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = [
            'email' => $request->get('email'),
            'password' => $request->get('password')
        ];

//        $credentials = request(['email', 'password']);
//        if (!$token = JWTAuth::attempt($credentials)) {
//            return response()->json(['error' => 'Unauthorized'], 401);
//        }
//
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
    }

    /**
     * 注册新用户
     */
    public function register(Request $request)
    {
//        密码验证
//        Hash::check($oldpassword, $res->password)
//        数据验证
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        // 读取参数并保存数据
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        // 创建Token并返回
        return $user;
    }

    /**
     * 获取经过身份验证的用户.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
//        $user = JWTAuth::parseToken()->authenticate();
//        return response()->json($user);
        return response()->json(auth('api')->user());
    }

    /**
     * 刷新Token.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function refresh()
    {
//        $old_token = JWTAuth::getToken();
//        $token = JWTAuth::refresh($old_token);
//        JWTAuth::setToken($token);
//        return $this->respondWithToken($token);
        return $this->respondWithToken(auth('api')->refresh());
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function refreshTest()
    {
        $array = auth('api')->payload()->jsonSerialize();
        $array['ttl'] = JWTAuth::factory()->getTTL();
        $array['refresh_ttl'] = JWTAuth::blacklist()->getRefreshTTL();
        print_r($array);
    }

    public function useTest()
    {
//        $token = auth('api')->login($user);
//        $token = JWTAuth::fromUser($user);
//        $token = auth('api')->tokenById(1);
//        $user = auth('api')->user();
//        $user = JWTAuth::parseToken()->authenticate();
//        辅助函数
        $exp = auth('api')->payload()->get('exp');
        $json = auth('api')->payload()->toJson();
        $array = auth('api')->payload()->jsonSerialize();
        $sub = $array['sub'];

//        Facade - 1
        $payload = JWTAuth::parseToken()->getPayload();
        $payload->get('sub'); // = 123
        $payload['jti']; // = 'asfe4fq434asdf'
        $payload('exp'); // = 123456
        $payload->toArray(); // = ['sub' => 123, 'exp' => 123456, 'jti' => 'asfe4fq434asdf'] etc
//        Facade - 2
        $exp = JWTAuth::parseToken()->getClaim('exp');

        print_r($json);
    }


}