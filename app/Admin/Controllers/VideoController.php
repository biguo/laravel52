<?php

namespace App\Admin\Controllers;


use App\Http\Controllers\Controller;
use App\Models\Video;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;


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

            $grid->column('full_name')->display(function () {
                $toStatus = [
                    '1' => ['3' => '下线'],
                    '2' => ['1' => '上线', '4' => '驳回'],
                    '3' => ['1' => '上线'],
                ];
                $baseStatus = [
                    '1' => '已上线',
                    '2' => '提交审核中',
                    '3' => '已下线',
                    '4' => '驳回'
                ];

                $jsScript = "let toStatus = " . json_encode($toStatus) . ";";
                $jsScript .= "let baseStatus = " . json_encode($baseStatus) . ";";
                $jsScript .= "let csrf_token = '" . csrf_token() . "';";
                $jsScript .= <<<EOT

        $(document).on('click', '.changeStatus', function(e) {
            let to = $(this).attr('data-to');
            let id = $(this).attr('data-id');
            let obj = $(this).parent();
            $(this).removeClass("changeStatus");
            changeStatus(to, obj, id);
        });

    function changeStatus(to, obj, id) {
        $.ajax({
            type: "GET",
            url:"changeStatus?table=video&id="+ id+"&_token="+ csrf_token +"&status="+ to,
            dataType:'json',
            success:function (data) {
                if(data.code === "200"){
                    changeButton(to, obj, id);
                    layer.msg('操作完成');
                }
            }
        })
    }

    function changeButton(num, obj, id){
        if(num === '4'){      // 驳回后刷新
            window.location.reload();
        }else{
            let barr = toStatus[num];
            let str = '<div style="font-size: 18px">' +  baseStatus[num] + '</div>';
            for (let item in barr){
                str += '<div class="changeStatus" data-to="'+ item +'" data-id="'+ id +'"  style="cursor:pointer">'+ barr[item] +'</div>'
            }
            obj.html(str)
        }
    }
EOT;
                Admin::js('/packages/layer-v3.1.1/layer/layer.js');
                Admin::script($jsScript);

                $str = '<div style="font-size: 18px">' . $baseStatus[$this->status] . '</div>';
                $toArr = $toStatus[$this->status];
                foreach ($toArr as $k => $v) {
                    $str .= '<div class="changeStatus" data-to="' . $k . '" data-id="' . $this->id . '" style="cursor:pointer" >' . $v . '</div>';
                }
                return $str;
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
            $form->image('pic', '图片');
            $form->file('url','视频');
        });
    }
}
