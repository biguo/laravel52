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

    public function pictures()
    {
        return responseSuccess([
            'http://upload.binghuozhijia.com/uploads/5f33813e91847/5f33813e91845.jpg',
            'http://upload.binghuozhijia.com/uploads/5f3381930b3ca/5f3381930b3c8.jpg',
            'http://upload.binghuozhijia.com/uploads/5f33827be0cea/5f33827be0ce8.jpg',
            'http://upload.binghuozhijia.com/uploads/5f3383302132a/5f33833021328.jpg',
            'http://upload.binghuozhijia.com/uploads/5f3383d3e4d4b/5f3383d3e4d49.jpg',
            'http://upload.binghuozhijia.com/uploads/5f33842f7d3cc/5f33842f7d3ca.jpg',
        ]);
    }



}
