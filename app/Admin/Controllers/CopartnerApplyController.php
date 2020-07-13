<?php

namespace App\Admin\Controllers;

use App\Models\CopartnerApply;

use App\Models\Member;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\Input;

class CopartnerApplyController extends Controller
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

            $content->header('合伙人套餐');
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

            $content->header('合伙人套餐');
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

            $content->header('合伙人套餐');
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
        return Admin::grid(CopartnerApply::class, function (Grid $grid) {
            $grid->model()->where('status', Status_Payed)->orderBy('id', 'desc');
            $grid->disableExport();
            $grid->disableRowSelector();
            $grid->id('ID')->sortable();
            $grid->phone('手机');
            $grid->wechat('微信号');
            $grid->bank('银行');
            $grid->bankNo('卡号');
            $grid->realname('真名');
            $grid->industry('行业');
            $grid->position('职位');
            $grid->trade_no('订单号');
            $grid->filter(function ($filter) {
                $filter->disableIdFilter();
                $filter->like('phone', 'Search phone');
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
        return Admin::form(CopartnerApply::class, function (Form $form) {

            $form->display('id', 'ID');
            $actions =Input::route()->getAction();
            $arr = explode('@', $actions['controller']);
            $currentAction = $arr[1];
            if(($currentAction === 'create') || $currentAction === 'store') {
                $form->text('phone', 'phone')->rules('required|min:11');
            }
            $form->text('wechat', '微信号')->rules('min:3');
            $form->text('bank', '银行');
            $form->text('bankNo', '账户');
            $form->text('realname', '真名');
            $form->text('industry', '行业');
            $form->text('position', '职位');
            if($currentAction !== 'create') { //新建
                $form->display('phone', 'phone');
                $form->display('trade_no', '订单号');
            }
            $form->saved(function (Form $form) use ($currentAction){
                if($currentAction === 'store'){ //新建
                    $order = $form->model();
                    $order->update(['status' => Status_Payed, 'trade_no' => 'CoP' . StrOrderOne()]);
                    $member = Member::getMemberByPhone($order->phone);
                    if(!$member){
                        Member::addNewMember(['phone' => $order->phone]);
                        $member = Member::getMemberByPhone($order->phone);
                    }
                    $member->update(['type' => 3]);
                    $member->increment('benefit', 10000);
                }
            });
        });
    }
}
