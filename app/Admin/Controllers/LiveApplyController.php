<?php

namespace App\Admin\Controllers;


use App\Admin\Extensions\CheckRow;
use App\Admin\Extensions\CustomerButton;
use App\Admin\Extensions\CustomerSwitch;
use App\Http\Controllers\Controller;
use App\Models\LiveApply;
use App\Models\Member;
use App\Models\Streamer;
use App\Models\Video;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Input;


class LiveApplyController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('直播房间申请');
            $content->description('description');
            $content->body($this->grid());
        });
    }

    /**
     * show interface.
     *
     * @param $id
     * @return Content
     */
    public function show($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('直播房间详情');
            $content->description('description');

            $show = Admin::form(LiveApply::class, function (Form $form) use ($id) {
                $LiveApply = LiveApply::find($id);
                $Streamer = Streamer::find($LiveApply->streamer_id);
                $Member = Member::find($Streamer->mid);

                $interface = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token=';
                $token = gettoken('wxdfe1d168b25d4fff',true);
                $shareImg = "<img src='".$interface . $token . "&media_id=" . $LiveApply->shareImg."' style='max-width:100px;max-height:100px' class='img img-thumbnail' />";
                $coverImg = "<img src='".$interface . $token . "&media_id=" . $LiveApply->coverImg."' style='max-width:100px;max-height:100px' class='img img-thumbnail' />";

                $form->display('phone', '手机号')->default($Member->phone);
                $form->display('nickname', '主播昵称')->default($Member->nickname);
                $form->display('wechat','主播微信号')->default($Streamer->wechat);
                $form->display('name', '房间名字');
                $form->html($shareImg, '直播间分享图');
                $form->html($coverImg, '直播间背景图');
                $form->display('startTime1', '直播计划开始时间')->default(date("Y-m-d H:i:s",$LiveApply->startTime));
                $form->display('endTime1', '直播计划结束时间')->default(date("Y-m-d H:i:s",$LiveApply->endTime));
                $form->tools(function (Form\Tools $tools) {
                    $tools->disableListButton();
                });
                $form->builder()->option('enableReset',0);  // 关闭reset按钮

                $sstatus = $Streamer->status;
                $streamerStatus = [
                    '1' => '上线中(未实名)',
                    '2' => '提交审核中',
                    '3' => '已下线',
                    '5' => '已实名'
                ];
                $form->display('status1', '主播状态')->default($streamerStatus[$sstatus]);

                $status = $LiveApply->status;
                $baseStatus = [
                    '1' => '已通过',
                    '2' => '申请中',
                    '4' => '驳回'
                ];

                $form->display('status2', '审核状态')->default($baseStatus[$status]);

                $passStreamer = ($Streamer->status !== 5) ? (new CustomerButton($id,'主播实名','/admin/streamerPass')) : '';
                $passLiveApply = ($Streamer->status === 5) && ($LiveApply->status !== 1) ? (new CustomerButton($id,'开通房间','/admin/toLive')) : '';
                $form->html($passStreamer . $passLiveApply);

            });

            $content->body($show->view($id));
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(LiveApply::class, function (Grid $grid) {
            $grid->model()->from('live_apply as a')
                ->Leftjoin('streamer as s', 'a.streamer_id', '=', 's.id')
                ->Leftjoin('iceland.ice_member as m', 'm.id', '=', 'a.mid')
                ->select('a.*', 'm.phone', 'm.nickname','s.wechat','s.status as sstatus', 's.wechat')

                ->orderBy('s.id', 'desc');

            $grid->disableExport();
            $grid->disableRowSelector();
            $grid->disableCreation();

            $grid->id('ID')->sortable();
            $grid->column('phone', '手机号');
            $grid->column('name', '房间名字');
            $grid->column('nickname', '主播昵称');
            $grid->column('wechat', '主播微信号');
            $grid->column('sstatus', '主播状态')->display(function ($sstatus) {
                $baseStatus = [
                    '1' => '上线中(未实名)',
                    '2' => '提交审核中',
                    '3' => '已下线',
                    '4' => '已驳回',
                    '5' => '已实名'
                ];
                return $baseStatus[$sstatus];
            });
            $grid->column('status', '审批状态')->display(function ($status) {
                $baseStatus = [
                    '1' => '已通过',
                    '2' => '申请中',
                    '4' => '驳回'
                ];
                return $baseStatus[$status];
            });
            $grid->column('startTime', '直播计划开始时间')->display(function ($startTime) {return date("Y-m-d H:i:s",$startTime);});
            $grid->column('endTime', '直播计划结束时间')->display(function ($endTime) {return date("Y-m-d H:i:s",$endTime);});
            $grid->created_at();
            $grid->updated_at();
            $grid->actions(function ($actions) {
                $actions->append('<a class="btn btn-sm btn-primary" href="'.admin_url('LiveApply').'/'.$actions->getKey().'/show"> 查看</a>');
                $actions->disableDelete();
                $actions->disableEdit();
            });
        });
    }

    /**
     * 主播实名确认
     * @return \Illuminate\Http\JsonResponse
     */
    public function pass()
    {
        $input = Input::except('_token');
        $LiveApply = LiveApply::find($input['id']);
        $Streamer = Streamer::find($LiveApply->streamer_id);
        $Streamer->status = 5;
        $Streamer->save();
        return responseSuccess();
    }


    /**
     * 开通直播房间
     * @return \Illuminate\Http\JsonResponse
     */
    public function toLive()
    {
        $input = Input::except('_token');
        $LiveApply = LiveApply::find($input['id']);
        $Streamer = Streamer::find($LiveApply->streamer_id);
        $Member = Member::find($LiveApply->mid);

        $data = $LiveApply->toArray();
        $data = array_except($data,['created_at', 'updated_at', 'id', 'mid', 'refuse_reason', 'status', 'streamer_id']);
        $data = array_merge($data, ['anchorName' => $Member->nickname, 'anchorWechat' => $Streamer->wechat]);

        $interface = 'https://api.weixin.qq.com/wxaapi/broadcast/room/create';
        $token = gettoken('wxdfe1d168b25d4fff', true);
        $url = $interface . "?access_token=" . $token;
        $json_data = JSON($data);
        $ret = doCurlPostRequest($url, $json_data, 'json');
        $ret = json_decode($ret, true);
        if($ret['errcode'] === 0){
            $LiveApply->status = 1;
            $LiveApply->save();
            return responseSuccess($ret);
        }else{
            return responseError($ret['errmsg']);
        }
    }
}
