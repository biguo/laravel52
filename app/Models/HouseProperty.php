<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HouseProperty extends Model
{
    protected $table = 'house_property';
    protected $connection = 'original';

    public $timestamps = false;

}
