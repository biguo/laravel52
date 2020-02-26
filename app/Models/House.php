<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Form;

class House extends Model
{
    protected $table = 'house';
    protected $connection = 'original';

    public $timestamps = false;


    public static function province($form, $id)
    {
        
    }
}
