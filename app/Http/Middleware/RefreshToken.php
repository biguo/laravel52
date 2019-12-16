<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use App\User;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class RefreshToken extends BaseMiddleware
{
    /***
     * Handle an incoming request.
     *
     * param  \Illuminate\Http\Request $request
     * param  \Closure $next
     * return mixed
     */
    public function handle($request, Closure $next)
    {
        // 使用 try 包裹，以捕捉 token 过期所抛出的 TokenExpiredException  异常
        try {
            if ($this->auth->parseToken()->authenticate()) {
                return $next($request);
            }
            return response()->json(['code' => 402, 'data' => [], 'msg' => '未登录']);

        } catch (TokenExpiredException $exception) {
            // 此处捕获到了 token 过期所抛出的 TokenExpiredException 异常，我们在这里需要做的是刷新该用户的 token 并将它添加到响应头中
            try {
                // 刷新用户的 token
                $token = $this->auth->refresh();
                // 使用一次性登录以保证此次请求的成功
//                Auth::guard('api')->onceUsingId($this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->toPlainArray()['sub']);
                $user_id = $this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->toPlainArray()['sub'];
                $user = auth('api')->onceUsingId($user_id);
                $request->headers->set('Authorization', 'Bearer ' . $token);
            } catch (JWTException $exception) {
                // 如果捕获到此异常，即代表 refresh 也过期了，用户无法刷新令牌，需要重新登录。
                //throw new UnauthorizedHttpException('jwt-auth', $exception->getMessage());
                return response()->json(['code' => 402, 'data' => [], 'msg' => $exception->getMessage()]);
            }
        }
//        这个问题答案简单，在response 的header中设置authorization。
//   关键点：后端一般使用的域名是二级域名比如我的是api.xx.com,会和前端产生一个跨域的影响，请记得一定要设置
//   `$response->headers->set('Access-Control-Expose-Headers', 'Authorization');`
//   设置跨域的时候还要设置一个Cache-Control,这个东西出现的问题真的是莫名其妙，坑了我很久..
//   `$response->headers->set('Cache-Control', 'no-store'); // 无的话会导致前端从缓存获取头token`
        return $this->setAuthenticationHeader($next($request), $token);
    }
}



