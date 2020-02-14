
<div id="uploader-demo" class="controls" >
    <div class="uploader-list @if(isset($class)) {{$class}} @else fileList @endif" style="float: left"> </div>
    {{--<button type="button" class="@if(isset($imageClass)) {{$imageClass}} @else filePicker @endif" tyle="float: left" >上传图片</button>--}}
    <div class="@if(isset($imageClass)) {{$imageClass}} @else filePicker @endif" style="width: 100px;height: 100px;" tyle="float: left">添加图片</div>
</div>