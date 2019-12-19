<?php

//api接口返回成功
function responseSuccess($data = array(), $message = '操作成功', $code = '200')
{
    return response()->json(['report' => 'ok', 'code' => $code, 'data' => $data, 'msg' => $message, 'action' => 'ACTION_NONE']);
}

//api接口返回失败
function responseError($message = '操作失败', $data = array(), $code = '500')
{
    return response()->json(['report' => 'fail', 'code' => $code, 'data' => $data, 'msg' => $message, 'action' => 'ACTION_NONE']);
}