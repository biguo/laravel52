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
            $grid->title('名称')->editable();
            $grid->image('图片')->image(Upload_Domain, 100, 100);
            $grid->column('unuse_image', '未使用图片')->image(Upload_Domain, 150, 100);
            $grid->column('used_image', '已使用图片')->image(Upload_Domain, 150, 100);
            $grid->price('价格')->editable();
            $grid->promotional_price('宣传价格')->editable();
            $grid->items('项目')->display(function ($items) {
                $items = array_map(function ($item) {
                    return "<span>{$item['title']}</span>";
                }, $items);
                return "<div style='width:500px'>".join('&nbsp;<br>', $items)."</div>";
            });
            $grid->sort()->editable()->sortable();
            $grid->status('状态')->switch();
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
            $form->number('price', '支付价格')->rules('required|regex:/^[0-9]\d*(\.\d+)?$/');  //
            $form->number('promotional_price', '宣传价格')->rules('required|regex:/^[0-9]\d*(\.\d+)?$/');  //
            $form->multipleSelect('items')->options(Item::all()->pluck('title', 'id'));
            $form->radio('will_refund', '入住之后退款')->options(['0' => '不能', '1'=> '可以'])->default('0');
            $form->hidden('country_id', 'country_id')->default($this->country);
            $form->hidden('sort');
            $form->hidden('status');
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
