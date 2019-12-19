<?php

//生成32位unicode
function genGuid(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"

        $uuid = substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);
        return $uuid;
    }
}

//api接口返回成功
function responseSuccess($data = array(), $message = '操作成功', $code = '200')
{
    return response()->json(['report' => 'ok', 'code' => $code, 'data' => $data, 'msg' => $message, 'action' => 'ACTION_NONE']);
}

function responseError($message = '操作失败', $data = array(), $code = '500')
{
    return response()->json(['report' => 'fail', 'code' => $code, 'data' => $data, 'msg' => $message, 'action' => 'ACTION_NONE']);
}

function responseSuccessArr($data = array(), $message = '操作成功', $code = '200')
{
    return ['report'=>'ok','code'=>$code,'data'=>$data,'msg'=>$message,'action'=>'ACTION_NONE'];
}

function responseErrorArr($message = '操作失败',$data = array(), $code = '500')
{
    return ['report'=>'fail','code'=>$code,'data'=>$data,'msg'=>$message,'action'=>'ACTION_NONE'];
}
