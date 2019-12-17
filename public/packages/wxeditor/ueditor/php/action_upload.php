<?php
/**
 * 上传附件和上传视频
 * User: Jinqn
 * Date: 14-04-09
 * Time: 上午10:17
 */
include "Uploader.class.php";


function post($url, $post_data = '', $timeout = 5) {//curl
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    if ($post_data != '') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $file_contents = curl_exec($ch);
    curl_close($ch);
    return $file_contents;
}

/* 上传配置 */
$base64 = "upload";
switch (htmlspecialchars($_GET['action'])) {
    case 'uploadimage':
        $url = $_SERVER['HTTP_ORIGIN']."/public/api/common/uploadImgUE";
        $post_data = [
            'file' => $_FILES['upfile']['tmp_name']
        ];
        $res = post($url, $post_data);
        return $res;
        break;
    case 'uploadscrawl':
        $config = array(
            "pathFormat" => $CONFIG['scrawlPathFormat'],
            "maxSize" => $CONFIG['scrawlMaxSize'],
            "allowFiles" => $CONFIG['scrawlAllowFiles'],
            "oriName" => "scrawl.png"
        );
        $fieldName = $CONFIG['scrawlFieldName'];
        $base64 = "base64";
        $up = new Uploader($fieldName, $config, $base64);
        return json_encode($up->getFileInfo());
        break;
    case 'uploadvideo':
        $config = array(
            "pathFormat" => $CONFIG['videoPathFormat'],
            "maxSize" => $CONFIG['videoMaxSize'],
            "allowFiles" => $CONFIG['videoAllowFiles']
        );
        $fieldName = $CONFIG['videoFieldName'];
        $up = new Uploader($fieldName, $config, $base64);
        return json_encode($up->getFileInfo());
        break;
    case 'uploadfile':
    default:
        $config = array(
            "pathFormat" => $CONFIG['filePathFormat'],
            "maxSize" => $CONFIG['fileMaxSize'],
            "allowFiles" => $CONFIG['fileAllowFiles']
        );
        $fieldName = $CONFIG['fileFieldName'];
        $up = new Uploader($fieldName, $config, $base64);
        return json_encode($up->getFileInfo());
        break;
}

/* 生成上传实例对象并完成上传 */
//$up = new Uploader($fieldName, $config, $base64);
// 当上传图片时为
/*
$up = new Uploader('upfile', [
    ['pathFormat' => "/ueditor/php/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}"],
    ['maxSize' => 2048000],
    ['allowFiles' => [".png", ".jpg", ".jpeg", ".gif", ".bmp"]]
], "upload");
*/

/**
 * 得到上传文件所对应的各个参数,数组结构
 * array(
 *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
 *     "url" => "",            //返回的地址
 *     "title" => "",          //新文件名
 *     "original" => "",       //原始文件名
 *     "type" => ""            //文件类型
 *     "size" => "",           //文件大小
 * )
 */

/* 返回数据 */
//return json_encode($up->getFileInfo());
