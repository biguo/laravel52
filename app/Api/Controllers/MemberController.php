<?php

namespace App\Api\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MemberController extends BaseController
{

    public function show($id)
    {
        print_r($id);
//        $user = User::find($id);
//        print_r($user);
//        return $this->response->item($user, new UserTransformer);
//        return $this->response->noContent();
//        return $this->response->error('This is an error.', 404);
//        return $this->response->errorUnauthorized();
    }

    public function create()
    {
        print_r('create');
    }
}
