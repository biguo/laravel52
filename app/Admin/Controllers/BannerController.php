<?php

namespace App\Admin\Controllers;


use App\Models\Banner;


use Illuminate\Support\Facades\Input;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class BannerController extends Controller
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

            $content->header('轮播图');
            $content->description('首页轮播图');

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

//        return view('banner.edit',[]);

        return Admin::content(function (Content $content) use ($id) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form()->edit($id));
        });
    }

//    public function update($id)
//    {
//        $params = Input::all();
//        echo "<pre>";
//        print_r($params);
//        exit;
////        admin_toastr(trans('admin::lang.update_succeeded'));
////        $url = Input::get('_previous_') ?: $this->resource(-1);
////        return redirect($url);
//    }

    public function update()
    {
        if(!is_null(request()->get('status'))){
            $status = request()->get('status')==='on'?1:0;
        }
        $data = request()->all();
        $id_arr = request()->route()->parameters();

        return $this->form()->update($id_arr['banner'],$data);
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('header');
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
        return Admin::grid(Banner::class, function (Grid $grid) {
//            $grid->model()->where('id','>','1');
//            $grid->column('username', '用户名');
//            $grid->paginate(15);
//            $grid->perPages([10, 20, 30, 40, 50]);

            $grid->id('ID')->sortable();
            $grid->title()->editable();
            $grid->image()->image('http://upload.binghuozhijia.com/', 100, 100);
            $grid->created_at();
            $grid->updated_at();
            $grid->filter(function ($filter) {
//                $filter->useModal();
                $filter->disableIdFilter();
                $filter->like('title', 'Search');
//                $filter->between('created_at', 'Created Time')->datetime();
//                $filter->where(function ($query) {
//                    $query->where('title', 'like', "%{$this->input}%")
//                        ->orWhere('content', 'like', "%{$this->input}%");
//                }, 'Search');
            });


            // 设置text、color、和存储值

            $states = [
                '1'  => ['value' => 1, 'text' => '打开', 'color' => 'primary'],
                '0' => ['value' => 0, 'text' => '关闭', 'color' => 'default'],
            ];
            $grid->status()->switch($states);
//            $grid->column('status', '状态')->display(function ($status) {
//                return $status ? '开启' : '关闭';
//            });





        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Banner::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('title', 'title')->rules('required|min:3');
            $form->ckeditor('content', 'content');
            $form->text('status', 'status');
            $form->image('image', 'image');
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
