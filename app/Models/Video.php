<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Video extends Model
{
    //指定表名
    protected $table = 'video';
    protected $guarded = [];

    /**
     * 列表 id倒排
     * @param null $mid
     * @return mixed
     */
    public function VideoPublishedList($mid = null)
    {
        return $this->VideoList($mid);
    }


    /**
     * 滑动加载  按点赞数倒排
     * @param null $mid
     * @return mixed
     */
    public function VideoPopularityList($mid = null, $source_id = null)
    {
        return $this->VideoList($mid, 10, 2, $source_id);
    }


    public function VideoList($mid = null, $paginate = 6, $order_type = 1, $source_id = null)
    {
        $query =  self::from('video as v')
            ->LeftJoin('iceland.ice_member as m', 'v.mid','=','m.id')
            ->LeftJoin('video_like as l', 'v.id','=','l.source_id')
            ->select('v.id','v.title','v.tags',
                DB::raw("CONCAT('".Upload_Domain."',v.url) as url"),
                DB::raw("CONCAT('".Upload_Domain."',v.pic) as pic"),
                DB::raw("IFNULL(m.nickname,'冰火之家') as  nickname"),
                DB::raw("IFNULL(m.headpic,'http://upload.binghuozhijia.com/uploads/5ee83b780a3fd/5ee83b780a3fb.jpg') as headpic"),
                DB::raw("COUNT(l.id) as like_count"),
                DB::raw('0 as height'))
            ->where([['v.status','=','1'],['v.project','=','乡村民宿']])
            ->groupBy('v.id');
        if($order_type === 1){
            $res = $query->orderBy('id', 'desc')->paginate($paginate);
        }else if($order_type === 2 ){
            if($source_id){
                $query = $query->orderBy(DB::raw("if(v.id =$source_id, 2, 1)"), 'desc');
            }
            $res = $query->orderBy(DB::raw("COUNT(l.id) "), 'desc')->paginate($paginate);
        }

        foreach ($res as $item){
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
