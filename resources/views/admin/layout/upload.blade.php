<link rel="stylesheet" type="text/css" href="{{ URL::asset('admin/assets2/vendor/upload/css/upload.css')}}">
<link rel="stylesheet" type="text/css" href="/packages/upload/css/upload.css">
<script src="http://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
<script type="text/javascript" src="/packages/upload/js/webuploader.js"></script>
<script type="text/javascript" src="/packages/upload/js/index.js"></script>
<div id="uploader-demo" class="controls" >
    <div class="uploader-list @if(isset($class)) {{$class}} @else fileList @endif" style="float: left"> </div>
    {{--<button type="button" class="@if(isset($imageClass)) {{$imageClass}} @else filePicker @endif" tyle="float: left" >上传图片</button>--}}
    <div class="@if(isset($imageClass)) {{$imageClass}} @else filePicker @endif" style="width: 100px;height: 100px;" tyle="float: left">添加图片</div>
</div>