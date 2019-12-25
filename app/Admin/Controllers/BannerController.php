<?php

namespace App\Admin\Controllers;


use App\Models\Banner;


use Illuminate\Support\Facades\DB;
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
            $grid->model()->where('country_id',$this->country)->orderBy('status', 'desc')->orderBy('sort')->orderBy('id', 'desc');
            $grid->disableExport();
            $grid->id('ID')->sortable();
            $grid->title()->editable();
            $grid->subtitle()->editable();
            $grid->description()->editable();
            $grid->image()->image(Upload_Domain, 100, 100);
            $grid->bigImage()->image(Upload_Domain, 100, 100);
            $grid->created_at();
            $grid->filter(function ($filter) {
//                $filter->useModal();
                $filter->disableIdFilter();
                $filter->like('title', 'Search');
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
            $form->text('subtitle', 'subtitle')->rules('required|min:3');
            $form->text('description', 'description')->rules('required|min:3');
            $form->image('image', 'image');
            $form->image('bigImage', '长图');
            $form->hidden('sort');
            $form->hidden('status');
            $form->hidden('country_id','country_id')->default($this->country);
        });
    }
}
