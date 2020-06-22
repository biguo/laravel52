<?php

namespace App\Admin\Controllers;


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
            $str = '<div class="modal fade" id="refuseModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <form class="form-inline" method="get" id="modalForm" action="'. URL('admin/changeStatus') .'">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <input type="hidden" id="modal-id" name="id" value="">
                            <input type="hidden" id="modal-status" name="status" value="4">
                            <h4 class="modal-title" id="myModalLabel" style="font-weight: 600;font-size:16px">不通过理由</h4>
                        </div>
                        <div class="modal-body">
                            <textarea id="modal-reason" name="refuse_reason"  cols="70" rows="7"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                            <button type="button" class="btn btn-primary msbtn">提交更改</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal -->
            </form>
        </div>';
            $content->body($str);

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
                $token = gettoken('wxdfe1d168b25d4fff');
                $shareImg = "<img src='".$interface . $token . "&media_id=" . $LiveApply->shareImg."' style='max-width:100px;max-height:100px' class='img img-thumbnail' />";
                $coverImg = "<img src='".$interface . $token . "&media_id=" . $LiveApply->coverImg."' style='max-width:100px;max-height:100px' class='img img-thumbnail' />";

                $form->display('id', 'ID');
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
//            1 通过  2 申请中  4 驳回
            $grid->column('状态')->display(function () {
                $toStatus = [
                    '2' => ['1' => '通过', '4' => '驳回'],
                    '4' => ['2' => '审核']
                ];
                $baseStatus = [
                    '1' => '已通过',
                    '2' => '申请中',
                    '4' => '驳回'
                ];
                return (new CustomerSwitch($this->id, $this->status, $toStatus,$baseStatus, 'live_apply'))->render();
            });
            $grid->created_at();
            $grid->updated_at();
            $grid->actions(function ($actions) {
                $actions->append('<a class="btn btn-sm btn-primary" href="'.admin_url('LiveApply').'/'.$actions->getKey().'/show"> 查看</a>');
                $actions->disableDelete();
                $actions->disableEdit();
            });
        });
    }

}
