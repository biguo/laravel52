<?php

namespace App\Admin\Controllers;

use App\Models\House;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class HouseController extends Controller
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

            $content->header('header');
            $content->description('description');

            $content->body($this->country);
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

//    public function update($id)
//    {
////        return $this->form()->update($id);
//        $data = Input::all();
//        print_r($data);
//
//
//        /* @var Model $this->model */
//        $this->model = $this->model->with($this->getRelations())->findOrFail($id);
//
//        $this->setFieldOriginalValue();
//
//        // Handle validation errors.
//        if ($validationMessages = $this->validationMessages($data)) {
//            return back()->withInput()->withErrors($validationMessages);
//        }
//
//        if (($response = $this->prepare($data)) instanceof Response) {
//            return $response;
//        }
//
//        DB::transaction(function () {
//            $updates = $this->prepareUpdate($this->updates);
//
//            foreach ($updates as $column => $value) {
//                /* @var Model $this->model */
//                $this->model->setAttribute($column, $value);
//            }
//
//            $this->model->save();
//
//            $this->updateRelation($this->relations);
//        });
//
//        if (($result = $this->complete($this->saved)) instanceof Response) {
//            return $result;
//        }
//
//        if ($response = $this->ajaxResponse(trans('admin::lang.update_succeeded'))) {
//            return $response;
//        }
//
//        return $this->redirectAfterUpdate();
//
//
//
//    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(House::class, function (Grid $grid) {
                $grid->model()->from('house as h')
//                ->join('admin_center_users as r', 'r.user_id', '=', 'u.id')
                    ->where('h.country_id', $this->country)
//                    ->select('h.title', 'h.id')
                    ->orderBy('h.id', 'desc');



            $grid->id('ID')->sortable();
            $grid->column('title', '房源名称')->editable();
            $grid->column('price', '价格');
        });
    }


    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {


        return House::form(House::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('title', '标题')->rules('required');
            $form->number('degression', '成本价')->rules('required|regex:/^[1-9]\d*(\.\d+)?$/')->default(0);
            $form->number('hostdayprice', '周末价格')->rules('required|regex:/^[1-9]\d*(\.\d+)?$/')->default(0);  //大于1的正数
            $form->number('roomarea', '房间面积')->rules('required|regex:/^[1-9]\d*(\.\d+)?$/')->default(0);  //大于1的正数
            $form->number('floor', '建筑总层数')->rules('required|regex:/^[0-9]\d*$/')->default(0);
            $form->number('livenum', '人数')->rules('required|regex:/^[0-9]\d*$/')->default(0);
            $form->number('roomnum', '卧室数量')->rules('required|regex:/^[0-9]\d*$/')->default(0);
            $form->number('hallnum', '客厅数量')->rules('required|regex:/^[0-9]\d*$/')->default(0);
            $form->number('toiletnum', '卫生间数量')->rules('required|regex:/^[0-9]\d*$/')->default(0);  //非负整数
            $form->number('kitchennum', '厨房数量')->rules('required|regex:/^[0-9]\d*$/')->default(0);  //非负整数
            $form->ckeditor ('content', '房间介绍');



//            $form->number('single', '单间9折入住券')->rules('required|regex:/^[0-9]\d*$/');  //非负整数
            $form->text('country_id')->value($this->country);

        });
    }
}
