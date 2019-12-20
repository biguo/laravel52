<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

class FileModel extends Model
{
    private $BASEURL ;
    private $ACCESS_KEY  ;
    private $SECRET_KEY ;
    private $BUCKET ;
    private $TOKEN ;
    private $uploadMgr ;
    private $bucketManager;

    public function __construct()
    {
        $qiniu = config('filesystems.disks.qiniu');
        $this->ACCESS_KEY = $qiniu['access_key'];
        $this->SECRET_KEY = $qiniu['secret_key'];
        $this->BUCKET = $qiniu['bucket'];
        $this->BASEURL = 'http://'.$qiniu['domains']['default'].'/';

        $this->uploadMgr = new UploadManager();
        $auth = new Auth($this->ACCESS_KEY, $this->SECRET_KEY);
        $this->TOKEN = $auth->uploadToken($this->BUCKET);
        $config = new \Qiniu\Config();
        $this->bucketManager = new BucketManager($auth,$config);
    }

    /**
     *  return url
     */
    public function uploads($filePath, $newName)
    {
        $key = 'uploads/' . uniqid() . '/' . $newName;
        list($ret, $err) = $this->uploadMgr->putFile($this->TOKEN, $key, $filePath);
        if ($err !== null) {
            return false;
        } else {
            return $this->BASEURL . $ret['key'];
        }
    }

    /**
     *  return url
     */
    public function uploadStream($data, $newName)
    {
        $key = 'uploads/' . uniqid() . '/' . $newName;
        list($ret, $err) = $this->uploadMgr->put($this->TOKEN, $key, $data);
        if ($err !== null) {
            return false;
        } else {
            return $this->BASEURL . $ret['key'];
        }
    }


    public function deleteImg($img)
    {
        if ($img){
            foreach ($img as $k=>$v){
                $res = $this->bucketManager->delete($this->BUCKET,$v);
            }
        }
    }

}
