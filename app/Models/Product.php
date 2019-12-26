<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Input;

class Product extends Model
{
    protected $table = 'product';

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public static function getById($product_id = null)
    {
        if (!$product_id) {
            $product_id = Input::get('product_id');
        }
        return self::where('id', $product_id)->first();
    }

}
