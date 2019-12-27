<?php

namespace App\Api\Controllers;

use App\Models\Country;
use App\Models\Member;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function getList(Request $request)
    {

        $products = Country::current()->usedProduct()->toarray();

        $chosen = [];
        foreach ($products as $data) {
            $str = $data['title'] . $data['price'] . ': ';
            if ($data['single'] > 0)
                $str .= "入住9折（单间）邀请券" . $data['single'] . "张;";
            if ($data['whole'] > 0)
                $str .= "整栋8.5折入住券" . $data['whole'] . "张;";
            if ($data['coffee'] > 0)
                $str .= "咖啡券" . $data['coffee'] . "张;";
            if ($data['wine'] > 0)
                $str .= "持卡人生日赠送香槟" . $data['wine'] . "瓶;";
            if ($data['cake'] > 0)
                $str .= "持卡人生日送小蛋糕" . $data['cake'] . "块;";
            $str = rtrim($str,';').'.';
            $data = array_only($data, ['id','image','icon']);
            $data['description'] = $str;
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
