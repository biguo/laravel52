<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InviteCode extends Model  #2020/5/9 废弃中
{
    protected $table = 'invite_code';
    protected $fillable = ['code', 'times'];
    public $timestamps = false;
}
