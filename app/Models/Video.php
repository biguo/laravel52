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
    public function VideoPublishedList($mid = null, $order_type = 1, $city = null)
    {
        return $this->VideoList($mid, 10, $order_type, null,  $city);
    }


    /**
     * 滑动加载  按点赞数倒排
     * @param null $mid
     * @return mixed
     */
    public function VideoPopularityList($mid = null, $source_id = null, $city = null)
    {
        return $this->VideoList($mid, 10, 2, $source_id, $city);
    }


    public function VideoList($mid = null, $paginate = 6, $order_type = 1, $source_id = null, $city = null)  # $order_type 1 id 倒序 2 点赞数倒排
    {
        $where = [['v.status','=','1'],['v.project','=','乡村民宿']];
        if($city !== null){
            array_push($where, ['city','=',$city]);
        }
        $query =  self::from('video as v')
            ->LeftJoin('iceland.ice_member as m', 'v.mid','=','m.id')
            ->LeftJoin('video_like as l', 'v.id','=','l.source_id')
            ->select('v.id','v.title','v.tags','v.sorted','v.city','v.mid',
                DB::raw("CONCAT('".Upload_Domain."',v.url) as url"),
                DB::raw("CONCAT('".Upload_Domain."',v.pic) as pic"),
                'm.nickname',
                'm.headpic',
                DB::raw("COUNT(l.id) as like_count"),
                DB::raw('0 as height'))
            ->where($where)
            ->groupBy('v.id');
        if($order_type === 1){
            $res = $query->orderBy('id', 'desc')->paginate($paginate);
        }else if($order_type === 2 ){
            if($source_id){
                $query = $query->orderBy(DB::raw("if(v.id =$source_id, 2, 1)"), 'desc');
            }
            $res = $query
                ->orderBy(DB::raw('sorted=0'), 'asc')
                ->orderBy('sorted', 'asc')
                ->orderBy(DB::raw("COUNT(l.id) "), 'desc')
                ->paginate($paginate);
        }

        foreach ($res as $item){
            $item->like = 0;
            $item->tags = ($item->tags)? explode(',',$item->tags): [];
            $item->follow = 0;
            if($mid){
                $like = VideoLike::where([['source_id','=',$item->id],['mid','=',$mid]])->count();
                if($like > 0){
                    $item->like = 1;
                }
                $follow = Follow::where([['followed','=',$item->mid],['mid','=',$mid]])->count();
                if($follow > 0){
                    $item->follow = 1;
                }
            }
        }
        return $res;
    }




}
