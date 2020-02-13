<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Form;

class House extends Model
{
    protected $table = 'house';
    protected $connection = 'original';

    public $timestamps = false;

    public static function form(\Closure $callback)  //使用自定义form表单  为了使用自定义的验证
    {
        Form::registerBuiltinFields();

        return new CustomerForm(new static(), $callback);
    }

    public static function province($form, $id)
    {
        
    }
}
