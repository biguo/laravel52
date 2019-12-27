<?php

namespace App\Admin\Controllers;

use App\Models\Product;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class ProductController extends Controller
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

            $content->header('充值产品');
            $content->description('充值及优惠福利');

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
        return Admin::grid(Product::class, function (Grid $grid) {
            $grid->model()->where('country_id',$this->country)->orderBy('status', 'desc')->orderBy('sort')->orderBy('id', 'desc');
            $grid->disableExport();
            $grid->disableRowSelector();
            $grid->id('ID')->sortable();
            $grid->title()->editable();
            $grid->image()->image(Upload_Domain, 100, 100);
            $grid->column('icon','图标')->image(Upload_Domain, 30, 30);
            $grid->price()->editable();
            $grid->filter(function ($filter) {
//                $filter->useModal();
                $filter->disableIdFilter();
                $filter->like('title', 'Search');
            });

            $grid->sort()->editable()->sortable();
            $grid->status()->switch();
            $grid->column('single','单间9折入住券');
            $grid->column('whole','整栋8.5折入住券');
            $grid->column('coffee','咖啡券');
            $grid->column('wine','香槟');
            $grid->column('cake','小蛋糕');

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
        return Admin::form(Product::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('title', 'title')->rules('required|min:3');
            $form->image('image', 'image');
            $form->image('icon', 'icon');
            $form->number('price', 'price')->rules('required|regex:/^[1-9]\d*(\.\d+)?$/');  //大于1的正数
            $form->number('single', '单间9折入住券')->rules('required|regex:/^[0-9]\d*$/');  //非负整数
            $form->number('whole', '整栋8.5折入住券')->rules('required|regex:/^[0-9]\d*$/');
            $form->number('coffee', '咖啡券')->rules('required|regex:/^[0-9]\d*$/');
            $form->number('wine', '持卡人生日赠送香槟')->rules('required|regex:/^[0-9]\d*$/');
            $form->number('cake', '持卡人生日送小蛋糕')->rules('required|regex:/^[0-9]\d*$/');
            $form->text('content', 'content');
            $form->hidden('country_id','country_id')->default($this->country);
            $form->hidden('sort');
            $form->hidden('status');
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
