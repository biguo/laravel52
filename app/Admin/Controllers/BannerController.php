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
            $grid->model()->where('admin_user_id',$this->mid)->orderBy('status', 'desc')->orderBy('sort')->orderBy('id', 'desc');
            $grid->disableExport();
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

            $grid->sort()->editable();
            $grid->status()->switch();
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
            $form->image('image', 'image');
            $form->hidden('sort');
            $form->hidden('status');
            $form->hidden('admin_user_id', 'admin_user_id')->default($this->mid);;
        });
    }
}
