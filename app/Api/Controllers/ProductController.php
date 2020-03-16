<?php

namespace App\Api\Controllers;

use App\Models\Country;
use App\Models\Item;
use App\Models\Member;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function getList(Request $request)
    {

        $products = Country::current()->usedProduct()->toarray();

        $chosen = [];
        foreach ($products as $data) {
            $data['items'] = Item::from('item as i')->join('product_item as r','i.id','=','r.item_id')->where('r.product_id', $data['id'])->select('title','description','main')->get()->toArray();
            $chosen[] = $data;
        }
        return responseSuccess($chosen);
    }

    /**
     *  提交订单之前的页面
     */
    public function orderShowBefore(Request $request)
    {
        if ($request->isMethod('GET')) {
            $mid = $this->checkLogin($request);
            if (!$mid) {
                return responseError('请登录');
            }
            $data['left'] = Member::getMemberById($mid)->leftamount;
            $data['product'] = Country::current()->usedProduct();
            return responseSuccess($data);
        }
    }


}
