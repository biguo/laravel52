<?php

namespace App\Admin\Extensions;

use Encore\Admin\Admin;

class CheckRow
{

    public function __construct()
    {
    }

    protected function script()
    {

        return <<<SCRIPT

$('.grid-check-row').on('change', function () {

    // Your code.
    let id = $(this).val();
    let token = $('#csrf').val();

    
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "Value" ,
        data: {_token:token,id:id},//传参
        success: function (data) {
//            window.location.reload();
        },
    });
});

SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());
        return "<input class='form-control grid-check-row'  />";
    }

    public function __toString()
    {
        return $this->render();
    }
}