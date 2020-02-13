<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HouseAttr extends Model
{
    protected $table = 'house_attr';
    protected $connection = 'original';

    public $timestamps = false;

    public static function checkboxs($form, $id, $key)
    {
        $name = HouseProperty::where([['alias','=',$key],['pid','=','0']])->value('name');
        $checks = $form->checkbox($key, $name)->options(HouseProperty::where([['alias','=',$key],['pid','!=','0']])
            ->pluck('name', 'id')->toArray());
        $attr = HouseAttr::where('houseid',$id)->first();
        if($attr){
            $arr = explode(',',$attr->$key);
            $checks->value($arr);
        }
    }

    public static function radios($form, $id, $key)
    {
        $name = HouseProperty::where([['alias','=',$key],['pid','=','0']])->value('name');
        $radios = $form->radio($key, $name)->options(HouseProperty::where([['alias','=',$key],['pid','!=','0']])
            ->pluck('name', 'id')->toArray());
        $attr = HouseAttr::where('houseid',$id)->first();
        if($attr){
            $radios->default($attr->$key);
        }
    }



}
