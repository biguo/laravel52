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
        let modal = $("#refuseModal");
        modal.on("show.bs.modal", function(e) {
            let btn = $(e.relatedTarget),
            id = btn.data("id");
            $('#modal-id').val(id);
            $('#modal-reason').val('');
        });

        $('.msbtn').click(function () {
            addText();
        });

        function addText() {
            let id = $('#modal-id').val();
            let to = $('#modal-status').val();
            let obj = $('#div_'+ id).parent();
            $.ajax({
                type: "POST",//方法类型
                dataType: "json",//预期服务器返回的数据类型
                url:"changeStatus?table=" + table +"&_token="+ csrf_token ,
                data: $("#modalForm").serialize(),//传参
                success: function (data) {
                    if(data.code === "200"){
                        layer.msg(data.msg);
                        $('#refuseModal').modal('hide')
                        changeButton(to, obj, id);
                    }
                    if(data.code === "500"){
                        layer.msg('操作失败');
                    }
                },
            });
        }

        $(document).on('click', '.changeStatus', function(e) {
            let to = $(this).attr('data-to');
            let id = $(this).attr('data-id');
            let obj = $(this).parent();
            $(this).removeClass("changeStatus");
            if(to !== '4'){
                changeStatus(to, obj, id);
            }
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
            let barr = toStatus[num];
            let str = '<div style="font-size: 18px" id="div_'+ id +'">' +  baseStatus[num] + '</div>';
            for (let item in barr){
                let target = item === '4' ? ' data-toggle="modal"  data-target="#refuseModal"':'';
                str += '<div class="changeStatus" data-to="'+ item +'" data-id="'+ id +'"  style="cursor:pointer" '+ target +' >'+ barr[item] +'</div>'
            }
            obj.html(str)
        }
SCRIPT;
        return $jsScript;
    }

    public function render()
    {
        Admin::js('/packages/layer-v3.1.1/layer/layer.js');
        Admin::script($this->script());
        $str = '<div style="font-size: 18px" id="div_'. $this->id .'">' . $this->baseStatus[$this->status] . '</div>';
        $toArr = $this->toStatus[$this->status];
        foreach ($toArr as $k => $v) {
            $target = ($k === 4) ? ' data-toggle=modal  data-target=#refuseModal':'';
            $str .= '<div class="changeStatus" data-to="' . $k . '" data-id="' . $this->id . '" style="cursor:pointer" '. $target .'>' . $v . '</div>';
        }
        return $str;
    }

    public function __toString()
    {
        return $this->render();
    }
}