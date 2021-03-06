<?php

namespace App\Admin\Controllers;


use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Tools\SmsCode\SmsCode;
use App\Models\Streamer;
use App\Models\Member;

class ExampleController extends Controller
{
    use ModelForm;

    public function changeStatus()
    {
        $input = Input::except('_token');
        $update = array_only($input, ['status', 'refuse_reason']);
        DB::table($input['table'])->where('id',$input['id'])->update($update);
        if($input['table'] === 'streamer'){
            $Streamer = Streamer::find($input['id']);
            $Member = Member::find($Streamer->mid);
            $templateId = '550880';
            if($input['status'] === '1'){
                $templateId = '552688';
            }
            (new SmsCode())->SendYunmsg($Member->phone, $templateId);
        }
        return responseSuccess($input);
    }


}
