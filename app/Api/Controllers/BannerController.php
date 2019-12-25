<?php

namespace App\Api\Controllers;

use App\Models\Country;
use App\Models\Member;

use Illuminate\Http\Request;

class BannerController extends BaseController
{

    public function getList(Request $request)
    {
        if ($request->isMethod('GET')) {
            if(Country::current())
                return responseSuccess(Country::current()->usedBanner());
        }
        return responseError('非法请求');
    }



}
