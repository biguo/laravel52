<?php

namespace App\Admin\Controllers;


use App\Admin\Extensions\CustomerSwitch;
use App\Http\Controllers\Controller;
use App\Models\Video;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Input;


class VideoController extends Controller
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

            $content->header('视频');
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

            $content->header('视频');
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

            $content->header('视频');
            $content->description('description');

            $content->body($this->form());
        });
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Video::class, function (Grid $grid) {
            $grid->model()->whereIn('status', [Status_Online_video, Status_Review_video, Status_Offline_video])->orderBy('id', 'desc');
            $grid->disableExport();
            $grid->disableRowSelector();

            $grid->id('ID')->sortable();
            $grid->column('title', '名称');
            $grid->column('pic', '图片')->image(Upload_Domain, 150, 100);

            $grid->column('状态')->display(function () {
                $toStatus = [
                    '1' => ['3' => '下线'],
                    '2' => ['1' => '上线', '4' => '驳回'],
                    '3' => ['1' => '上线'],
                    '4' => ['2' => '审核'],
                ];
                $baseStatus = [
                    '1' => '已上线',
                    '2' => '提交审核中',
                    '3' => '已下线',
                    '4' => '驳回'
                ];
                return (new CustomerSwitch($this->id, $this->status, $toStatus,$baseStatus ,'video'))->render();
            });
            $grid->created_at();
            $grid->updated_at();
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
            $form->text('title', '标题')->rules('required');
            $form->image('pic', '图片')->rules('required');;
            $form->file('url','视频')->options(['initialPreviewConfig'  => [[ 'type' => 'video', 'filetype' => 'video/mp4']]])->rules('required');
            $form->checkbox('tags','行在旅途')->options([
                '呼吸自然'=> '呼吸自然',
                '夏日避暑地'=> '夏日避暑地',
                '在美丽乡村当村民吧'=> '在美丽乡村当村民吧',
                '旅途中的最美夜景'=> '旅途中的最美夜景',
                '值得去的古镇乡村'=> '值得去的古镇乡村',
                '拍照超美的打卡地'=> '拍照超美的打卡地',
                '我的旅行vlog'=> '我的旅行vlog'
            ]);
            $form->checkbox('tags','美食推荐')->options([
                '自然风味美食'=> '自然风味美食',
                '我的私家食堂'=> '我的私家食堂',
                '当地才能吃到的美食'=> '当地才能吃到的美食',
                '家乡小吃我来pick'=> '家乡小吃我来pick',
                '浪漫约会餐'=> '浪漫约会餐',
                '最爱下午茶时光'=> '最爱下午茶时光',
                '这个酒吧有点燃'=> '这个酒吧有点燃'
            ]);
            $form->checkbox('tags','精彩民宿')->options([
                '轰趴必去的民宿啊'=> '轰趴必去的民宿啊',
                '少女心爆棚的民宿'=> '少女心爆棚的民宿',
                '乡村里的民宿'=> '乡村里的民宿',
                '这些民宿风景真赞'=> '这些民宿风景真赞',
                '和萌娃一起的亲子民宿'=> '和萌娃一起的亲子民宿',
                '夏日度假避暑首选'=> '夏日度假避暑首选',
                '性价比超高的民宿'=> '性价比超高的民宿'
            ]);

            $form->ignore(['tags']);
            $form->saved(function (Form $form){
                $params = Input::all();
                if(!empty($params['tags']) && ( $params['tags'] = array_filter($params['tags'])) && ($params['tags']  = implode(',', $params['tags'] ))){
                    $form->model()->update(['tags' => $params['tags']]);
                }
                $actions =Input::route()->getAction(); //获得当前action位置
                if(isset($actions['controller']) && ($arr = explode('@', $actions['controller'])) && ($currentAction = $arr[1])){
                    if($currentAction === 'store'){ //新建
                        $form->model()->update(['status' => Status_Review_video, 'project' => '乡村民宿', 'mid' => 0]);
                    }
                }
            });
        });
    }
}
