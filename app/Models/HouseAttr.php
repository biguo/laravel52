<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HouseAttr extends Model
{
    protected $table = 'attr';
    protected $connection = 'original';

    public $timestamps = false;

}
