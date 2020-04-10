<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Product;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Input;

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
            $grid->model()->where('country_id', $this->country)->orderBy('status', 'desc')->orderBy('sort')->orderBy('id', 'desc');
            $grid->disableExport();
            $grid->disableRowSelector();
            $grid->id('ID')->sortable();
            $grid->title()->editable();
            $grid->image()->image(Upload_Domain, 100, 100);
            $grid->column('unuse_image', '未使用图片')->image(Upload_Domain, 150, 100);
            $grid->column('used_image', '已使用图片')->image(Upload_Domain, 150, 100);
            $grid->price()->editable();
            $grid->items()->display(function ($items) {
                $items = array_map(function ($item) {
                    return "<span class='label label-success'>{$item['title']}</span>";
                }, $items);
                return join('&nbsp;', $items);
            });
            $grid->weekend('周末使用')->display(function ($weekend) {
                return $weekend ? '是' : '否';
            });
            $grid->sort()->editable()->sortable();
            $grid->status()->switch();
            $grid->filter(function ($filter) {
//                $filter->useModal();
                $filter->disableIdFilter();
                $filter->like('title', 'Search');
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
        return Admin::form(Product::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('title', 'title')->rules('required|min:3');
            $form->image('image', 'image');
            $form->image('unuse_image', '未使用图片');
            $form->image('used_image', '已使用图片');
            $form->number('price', 'price')->rules('required|regex:/^[1-9]\d*(\.\d+)?$/');  //大于1的正数
            $form->multipleSelect('items')->options(Item::all()->pluck('title', 'id'));

            $form->radio('weekend', '是否能在周末使用')->options(['0' => '不能', '1'=> '可以'])->default('0');
            $form->hidden('country_id', 'country_id')->default($this->country);
            $form->hidden('sort');
            $form->hidden('status');
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
