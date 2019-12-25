<?php

namespace App\Api\Controllers;

use App\Models\Country;
use App\Models\Member;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function getList(Request $request)
    {
//        if ($request->isMethod('GET')) {
//            $data = $request->all();
//            $country = Country::current();
//            if($country)
//                return responseSuccess($country->usedBanner());
//        }
        return responseError('非法请求');
    }

    /**
     *  提交订单之前的页面
     */
    public function orderShowBefore(Request $request)
    {
        if($request->isMethod('GET')) {
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
