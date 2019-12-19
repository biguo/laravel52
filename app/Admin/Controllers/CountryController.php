<?php

namespace App\Admin\Controllers;


use App\Admin\Extensions\CheckRow;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Role;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

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
            $content->body($this->grid().'<input id="csrf" name="csrf" value="'.csrf_token().'">');
        });
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Administrator::class, function (Grid $grid) {

            $grid->model()->leftJoin('admin_role_users','admin_users.id','=','admin_role_users.user_id')
                ->leftJoin('admin_roles','admin_roles.id','=','admin_role_users.role_id')
                ->where('admin_roles.slug','country')->select('admin_users.id','admin_users.name as username','admin_roles.id as role_id');

            $grid->disableCreation();
            $grid->disableFilter();
            $grid->disableExport();
            $grid->disableRowSelector();

            $grid->column('id', 'ID');
            $grid->column('username', '用户名');
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->append(new CheckRow($actions->getKey()));
            });

        });
    }
}
