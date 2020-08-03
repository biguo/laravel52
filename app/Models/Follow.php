<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    //指定表名
    protected $table = 'follow';
    protected $guarded = [];
    public $timestamps = false;
}
