<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'order';
    protected $fillable = ['title', 'price', 'image', 'country_id', 'product_id', 'mid', 'trade_no', 'single', 'whole', 'coffee', 'wine', 'cake'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'mid');
    }

    /**
     * @param $all // 所需的参数
     */
    public function AddOrder($all)
    {
        $product = Product::getById();
        if ($product) {
            if (number_format($product->price, 2) != number_format($all['total'], 2)) {
                return responseError("计算金额不对");
            }
            $data = array_only($product->toarray(), ['title', 'price', 'image', 'country_id', 'single', 'whole', 'coffee', 'wine', 'cake']);
            $all = array_except($all, ['total']);
            $data = array_merge($data, $all);
            $data['trade_no'] = 'Add' . StrOrderOne();
            $flag = self::create($data);
            if ($flag) {
                return responseSuccess(["msg" => "订单提交成功", "trade_no" => $data['trade_no']]);
            } else {
                return responseError("订单提交失败!");
            }
        } else {
            return responseError("商品不存在!!");
        }
    }


}
