<?php

namespace App\Admin\Controllers;


use App\Admin\Extensions\CustomerSwitch;
use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Streamer;
use App\Models\Video;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Input;


class StreamerController extends Controller
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

            $content->header('直播');
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
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('直播');
            $content->description('description');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('直播');
            $content->description('description');

            $content->body($this->form());
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

            $content->header('详情');
            $content->description('description');

            $show = Admin::form(Streamer::class, function (Form $form) use ($id) {
                $Streamer = Streamer::find($id);
                $Member = Member::find($Streamer->mid);
                $form->display('id', 'ID');
                $form->display('phone', '手机号')->default($Member->phone);
                $form->display('nickname', '昵称')->default($Member->nickname);
                $form->display('realname', '姓名')->default($Member->realname);
                $form->display('type', '类型');
                $form->display('experience', '经验');
                $form->display('introduce', '介绍');
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
        return Admin::grid(Streamer::class, function (Grid $grid) {
            $grid->model()->from('streamer as s')
                ->Leftjoin('iceland.ice_member as m', 'm.id', '=', 's.mid')
                ->select('s.*', 'm.phone', 'm.nickname', 'm.realname')
                ->whereNotIn('s.status', [Status_Reject_streamer])->orderBy('s.id', 'desc');

            $grid->disableExport();
            $grid->disableRowSelector();
            $grid->disableCreation();

            $grid->id('ID')->sortable();
            $grid->column('phone', '手机号');
            $grid->column('nickname', '昵称');
            $grid->column('realname', '名字');
            $grid->column('type', '类型');
            $grid->column('experience', '经验');
            $grid->column('introduce', '介绍');

            $grid->column('状态')->display(function () {
                $toStatus = [
                    '1' => ['3' => '下线','5' => '实名'],
                    '2' => ['1' => '通过', '4' => '驳回'],
                    '3' => ['1' => '上线'],
                    '4' => ['1' => '审核'],
                    '5' => ['1' => '取消实名'],
                ];
                $baseStatus = [
                    '1' => '上线中',
                    '2' => '提交审核中',
                    '3' => '已下线',
                    '4' => '已驳回',
                    '5' => '已实名'
                ];
                return (new CustomerSwitch($this->id, $this->status, $toStatus,$baseStatus, 'streamer'))->render();
            });
            $grid->created_at();
            $grid->updated_at();
            $grid->actions(function ($actions) {
                $actions->append('<a class="btn btn-sm btn-primary" href="'.admin_url('streamer').'/'.$actions->getKey().'/show"> 查看</a>');
                $actions->disableDelete();
                $actions->disableEdit();
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Video::class, function (Form $form) {
            $form->display('id', 'ID');
        });
    }
}
