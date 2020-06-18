<?php

namespace App\Admin\Extensions;

use Encore\Admin\Admin;

class CustomerSwitch
{

    protected $toStatus;
    protected $baseStatus;
    protected $id;
    protected $status;
    protected $table;

    public function __construct($id, $status, $toStatus, $baseStatus, $table)
    {
        $this->id = $id;
        $this->status = $status;
        $this->toStatus = $toStatus;
        $this->baseStatus = $baseStatus;
        $this->table = $table;
    }

    protected function script()
    {
        $jsScript = "let toStatus = " . json_encode($this->toStatus) . ";";
        $jsScript .= "let baseStatus = " . json_encode($this->baseStatus) . ";";
        $jsScript .= "let csrf_token = '" . csrf_token() . "';";
        $jsScript .= "let table = '" . $this->table . "';";
        $jsScript .= <<<SCRIPT

        $(document).on('click', '.changeStatus', function(e) {
            let to = $(this).attr('data-to');
            let id = $(this).attr('data-id');
            let obj = $(this).parent();
            $(this).removeClass("changeStatus");
            changeStatus(to, obj, id);
        });

        function changeStatus(to, obj, id) {
            $.ajax({
                type: "GET",
                url:"changeStatus?table=" + table + "&id="+ id+"&_token="+ csrf_token +"&status="+ to,
                dataType:'json',
                success:function (data) {
                    if(data.code === "200"){
                        changeButton(to, obj, id);
                        layer.msg('操作完成');
                    }
                }
            })
        }

        function changeButton(num, obj, id){
            if (typeof toStatus[num] === 'undefined'){
                window.location.reload();
            }else{
                let barr = toStatus[num];
                let str = '<div style="font-size: 18px">' +  baseStatus[num] + '</div>';
                for (let item in barr){
                    str += '<div class="changeStatus" data-to="'+ item +'" data-id="'+ id +'"  style="cursor:pointer">'+ barr[item] +'</div>'
                }
                obj.html(str)
            }
        }
SCRIPT;
        return $jsScript;
    }

    public function render()
    {
        Admin::js('/packages/layer-v3.1.1/layer/layer.js');
        Admin::script($this->script());
        $str = '<div style="font-size: 18px">' . $this->baseStatus[$this->status] . '</div>';
        $toArr = $this->toStatus[$this->status];
        foreach ($toArr as $k => $v) {
            $str .= '<div class="changeStatus" data-to="' . $k . '" data-id="' . $this->id . '" style="cursor:pointer" >' . $v . '</div>';
        }
        return $str;
    }

    public function __toString()
    {
        return $this->render();
    }
}