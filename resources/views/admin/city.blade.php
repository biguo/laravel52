@php
    $city = [];
    $area = [];

    if($id){
        $one = \App\Models\House::where('id', $id)->select('cityid', 'areaid')->first();

        $city = DB::connection('original')->table("districts")->where("id","=",$one->cityid)->pluck('name', 'id');
        $area = DB::connection('original')->table("districts")->where("id","=",$one->areaid)->pluck('name', 'id');

    }
    $form->select('cityid', '城市')->options(['请选择'] + $city )->rules('required|regex:/^[1-9]\d*(\.\d+)?$/');
    $form->select('areaid', '区')->options(['请选择'] + $area)->rules('required|regex:/^[1-9]\d*(\.\d+)?$/');
@endphp


<script>
    // 联动
    function nextSelect(upid, downid, url){
        $("." + upid).on("change", function(e) {
            console.log(1111);
            // $.get(
            //     url ,{id: $(this).select2("val")},
            //     function(data) {
            //         let $select = $("." + downid);
            //         let instance = $select.data('select2');
            //         if(instance){
            //             $select.select2('destroy').empty();
            //         }
            //         $("." + downid).select2({data: data.data});
            //     }
            // );
        });
    }

    nextSelect('provinceid','cityid','/api/getCityAndArea');
    nextSelect('cityid','areaid','/api/getCityAndArea');
</script>

