<?php

namespace App\Api\Controllers;

use App\Models\Country;


class BannerController extends BaseController
{

    public function getList()
    {
        if(Country::current())
                return responseSuccess(Country::current()->usedBanner());
        return responseError('非法请求');
    }



}
