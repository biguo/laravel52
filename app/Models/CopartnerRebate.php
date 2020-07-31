<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redis;

class CopartnerRebate extends Model  # 废弃
{
    protected $table = 'copartner_rebate';
    protected $guarded = [];
    public $timestamps = false;

    public static function dataList($mid)
    {
        return self::where('pid',$mid)->select('id','mid','pid','nickname','headpic', DB::raw('sum(rebate_amount) as rebate_amount'))->groupBy('mid')->get();
    }

}
