<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoLike extends Model
{
    //指定表名
    protected $table = 'video_like';
    protected $guarded = [];
    public $timestamps = false;
}
