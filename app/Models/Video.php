<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    //指定表名
    protected $table = 'video';
    protected $guarded = [];

    public function VideoPublishedList($mid = null)
    {
        $res =  self::from('video as v')->join('iceland.ice_member as m', 'v.mid','=','m.id')
            ->select('v.*','m.nickname','m.phone','m.realname')
            ->where([['v.status','=','1'],['v.project','=','乡村民宿']])
            ->orderBy('id', 'desc')->paginate(6);
        foreach ($res as $item){

            $item->url = Upload_Domain .$item->url;
            $item->pic = Upload_Domain .$item->pic;
            $item->like_count = VideoLike::where([['source_id','=',$item->id]])->count();
            $item->like = 0;
            $item->tags = ($item->tags)? explode(',',$item->tags): [];
            if($mid){
                $like = VideoLike::where([['source_id','=',$item->id],['mid','=',$mid]])->count();
                if($like > 0){
                    $item->like = 1;
                }
            }
        }
        return $res;

    }
}
