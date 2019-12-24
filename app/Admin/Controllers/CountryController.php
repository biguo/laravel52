<?php

namespace App\Admin\Controllers;


use App\Admin\Extensions\CheckRow;
use App\Models\Country;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Role;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\MessageBag;

class CountryController extends Controller
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
            $content->header('小程序设置');
            $content->description('度假村小程序设置');
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
        return Admin::grid(Country::class, function (Grid $grid) {

            $grid->model()->from('country as c')->leftJoin('admin_users as u','u.id','=','c.admin_user_id')
                ->select('c.id','c.title','c.appid','c.appsecret','c.status','u.name as username');

//            $grid->disableCreation();
            $grid->disableFilter();
            $grid->disableRowSelector();
//            $grid->disableActions();
            $grid->column('id', 'ID');
            $grid->column('title', '项目名')->editable();
            $grid->column('appid', '小程序id')->editable();
            $grid->column('appsecret', '小程序secret')->editable();
            $grid->column('username', '设置管理员');

            $grid->status()->switch();

        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Country::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('slug', 'slug')->rules('required|min:3');
            $form->text('title', 'title')->rules('required|min:3');
            $form->text('appid', '小程序id');
            $form->text('appsecret', '小程序secret');
            $form->hidden('status');
            $form->select('admin_user_id')->options(function () {
                return Administrator::pluck('name', 'id');
            })->default($this->mid);
//            $form->html(new CheckRow());
//            $form->saving(function (Form $form) {


//                $error = new MessageBag([
//                    'title'   => 'Error',
//                    'message' => '重点不是时间',
//                ]);
//                return back()->withInput()->with(compact('error'));

//            });

        });
    }
}
