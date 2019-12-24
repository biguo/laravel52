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
            $data = $request->all();
            $country = Country::where('slug', $data['slug'])->first();
            if($country)
                return responseSuccess($country->usedBanner());
        }
        return responseError('非法请求');
    }



}
