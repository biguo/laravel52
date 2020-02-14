<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Form\YuntuMap;
use App\Models\House;

use App\Models\HouseAttr;
use App\Models\HouseProperty;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Validator;

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

            $content->body($this->form($id)->edit($id));
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



    public function update($id)
    {
//        return $this->form()->update($id);

        echo "<pre>";
        $data = Input::all();
//        $model = $this->form()->model();
//        print_r($model);
        print_r($data);
        exit;
        if ($validationMessages = $this->validationMessages($data)) {
            return back()->withInput()->withErrors($validationMessages);
        }





        /* @var Model $this->model */
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

        exit;

    }

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
    protected function form($id = null)
    {
        return House::form(function (Form $form) use ($id){
            $form->html($id);

            $form->display('id', 'ID');
            $form->text('title', '标题')->rules('required');
            $form->number('degression', '成本价')->rules('required|regex:/^[1-9]\d*(\.\d+)?$/')->default(0);
            $form->number('hostdayprice', '周末价格')->rules('required|regex:/^[1-9]\d*(\.\d+)?$/')->default(0);  //大于1的正数
            $form->number('roomarea', '房间面积')->rules('required|regex:/^[1-9]\d*(\.\d+)?$/')->default(0);  //大于1的正数
            $form->number('floor', '建筑总层数')->rules('required|regex:/^[1-9]\d*$/')->default(0);
            $form->number('livenum', '人数')->rules('required|regex:/^[1-9]\d*$/')->default(0);
            $form->number('roomnum', '卧室数量')->rules('required|regex:/^[1-9]\d*$/')->default(0);
            $form->number('hallnum', '客厅数量')->rules('required|regex:/^[0-9]\d*$/')->default(0);
            $form->number('toiletnum', '卫生间数量')->rules('required|regex:/^[0-9]\d*$/')->default(0);  //非负整数
            $form->number('kitchennum', '厨房数量')->rules('required|regex:/^[0-9]\d*$/')->default(0);  //非负整数

            $province = DB::connection('original')->table("districts")->where("pid","=",0)->pluck('name', 'id');
            $form->select('provinceid', '省份')->options(['请选择'] + $province)->rules('required|regex:/^[1-9]\d*(\.\d+)?$/');
            $form->html(view('admin.city', ['form' => $form, 'id' => $id])->render());

            HouseAttr::checkboxs($form, $id,'kitchenids');
            HouseAttr::radios($form, $id,'category');

            $form->html(view('admin.image')->render(), '图片');

            $form->text('longi', '经度')->rules('required|regex:/^[0-9]\d*$/');
            $form->text('lati', '纬度')->rules('required|regex:/^[0-9]\d*$/');
            $form->text('address', '地址')->rules('required');
            $form->html(view('admin.yuntu')->render(), '地图定位');
            $form->ckeditor ('content', '房间介绍');

            $form->text('country_id')->value($this->country);

            $form->html('<style>.form-horizontal .checkbox, .form-horizontal .radio{float: left;}</style>' ); // 单选框一行
        });
    }
}
