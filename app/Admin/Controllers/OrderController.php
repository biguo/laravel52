<?php

namespace App\Admin\Controllers;

use App\Models\Order;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
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

            $content->header('订单列表');
            $content->description('订单列表');

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
        return Admin::grid(Order::class, function (Grid $grid) {
            $grid->model()->from('order as o')->leftJoin('member as m','m.id','=','o.mid')->select('m.phone','o.*')
                ->where('o.country_id',$this->country)->orderBy('o.status', 'desc')->orderBy('o.id', 'desc');

            $statusArr = ['1' => '待支付','2' => '已支付','4' => '已取消'];
            $grid->disableCreation();
            $grid->id('ID')->sortable();

            $grid->trade_no();
            $grid->column('phone','用户手机号');
            $grid->title();
            $grid->price();
            $grid->image()->image(Upload_Domain, 100, 100);
            $grid->column('status', '状态')->display(function ($status) use($statusArr){
                return $statusArr[$status];
            });
            $grid->created_at();
//            $grid->updated_at();
            $grid->filter(function ($filter) {
//                $filter->useModal();
                $filter->disableIdFilter();
                $filter->like('title', 'SearchTitle');
//                $filter->between('created_at', 'Created Time')->datetime();
//                $filter->where(function ($query) {
//                    $query->where('title', 'like', "%{$this->input}%")
//                        ->orWhere('content', 'like', "%{$this->input}%");
//                }, 'Search');
            });
            $grid->actions(function ($actions) {
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
        return Admin::form(Order::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
