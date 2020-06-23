<?php

namespace App\Admin\Extensions;

use Encore\Admin\Admin;

class CustomerButton
{

    protected  $id;
    protected  $text;
    protected  $url;

    public function __construct($id,$text, $url, $table = '', $status = '')
    {
        $this->id = $id;
        $this->text = $text;
        $this->url = $url;
    }

    protected function script()
    {
        $jsScript = "let url = '" . $this->url . "';";
        $jsScript .= "let token = '" . csrf_token() . "';";
        $jsScript .= <<<SCRIPT
$('.customer').on('click', function () {
    let id = $(this).attr('data-id');
    
    $.ajax({
        type: "POST",
        dataType: "json",
        url: url ,
        data: {_token:token,id:id},//传参
        success: function (data) {
            if(data.code === "200"){
                layer.msg('操作完成');
                window.location.reload();
            }else{
                layer.msg('操作失败');
            }
        },
    });
    
});

SCRIPT;
        return $jsScript;
    }

    protected function render()
    {
        Admin::js('/packages/layer-v3.1.1/layer/layer.js');
        Admin::script($this->script());
        return '<a class="btn btn-sm btn-primary customer" data-id="'.$this->id.'" style="margin-right:20px">'.$this->text.'</a>';

    }

    public function __toString()
    {
        return $this->render();
    }
}