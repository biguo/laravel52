

<div class="block-flat">

    <div class="form-group">
        <div class="col-sm-10">
            @include("admin.layout.upload")
        </div>
    </div>

</div>


<script>

    upload("house",300,"houseimage","fileList","filePicker");
    function upload(table,imgmax,fileName,list,filePicker){
        //   alert(123);
        var  $list = list ? list : 'fileList';
        var  $filePicker = filePicker ? filePicker : 'filePicker';
        var $ = jQuery,
            $list = $("."+$list),
            $filePicker = $("."+$filePicker), // 上传按钮
            $upimgmax = imgmax, // 支持上传最大个数
            $upimgindex = 0, // 支持上传最大个数
            // 优化retina, 在retina下这个值是2
            ratio = window.devicePixelRatio || 1,
            // 缩略图大小
            thumbnailWidth = 110 * ratio,
            thumbnailHeight = 110 * ratio,
            // 初始化Web Uploader
            uploader = WebUploader.create({
                // 自动上传。
                auto: true,
                // swf文件路径
                swf: 'webuploader/Uploader.swf',
                // 文件接收服务端。
                server: "{{ URL('api/common/img') }}?_token={{csrf_token()}}&table="+table,//你的后台图片上传接受地址 也就是uploadurl,  //  这里是图片上传地址
                // 选择文件的按钮。可选。
                // 内部根据当前运行是创建，可能是input元素，也可能是flash
                pick: {
                    id: $filePicker,
                    // multiple: false
                },
                duplicate: true,
                fileSingleSizeLimit: 5242880, //  单个文件大小
                fileNumLimit: $upimgmax, // 限制上传个数
                accept: {
                    title: 'Images',
                    extensions: 'jpg,jpeg,png',
                    mimeTypes: 'image/jpg,image/jpeg,image/png' //修改这行
                },
                thumb: {
                    width: 90,
                    height: 90,
                    // 图片质量，只有type为`image/jpeg`的时候才有效。
                    quality: 70,
                    // 是否允许放大，如果想要生成小图的时候不失真，此选项应该设置为false.
                    allowMagnify: true,
                    // 是否允许裁剪。
                    crop: true,
                    // 为空的话则保留原有图片格式。
                    // 否则强制转换成指定的类型。
                    type: 'image/jpeg'
                }
            });

        // 当有文件添加进来的时候
        uploader.on('fileQueued', function(file) {
            $upimgindex++;
            var $li = $(
                '<div id="' + file.id + '" class="file-item thumbnail">' +
                // '<input  class = ""  placeholder="图片描述"  name="'+fileName+'[-'+$upimgindex+'][describe]" value=""  >' +
                '<select class="houseimgs" name="' + fileName +'[-' + $upimgindex +'][describe]"><option value="1">封面</option><option value="2">周边</option><option value="3">卧室</option><option value="4">客厅</option><option value="5">厨房</option><option value="6">卫浴</option><option value="7">其它</option></select>' +
                '<img> <input type="hidden" name="'+fileName+'[-'+$upimgindex+'][url]" />' +
                '<div class="info">' + file.name + '</div>' +
                '<div class = "file-panel" style = "height: 30px;top:118px;" > ' +
                '<span class = "cancel delimgbtns" title="删除"> 删除</span></div>'
                ),
                $img = $li.find('img');
            $list.append($li);
            bindRemovefiles(file); // 文件删除
            // 创建缩略图
            uploader.makeThumb(file, function(error, src) {
                if (error) {
                    $img.replaceWith('<span>不能预览</span>');
                    return;
                }
                //alert();
                // console.log(src);
                $img.attr('src', src);
            }, thumbnailWidth, thumbnailHeight);
            var uploadfilesNum = uploader.getStats().queueNum; //  共选中几个图片
            // 最多支持 $upimgmax张
            if ($('.file-item').length >= $upimgmax) {
                $filePicker.hide();
                if ($('.file-item').length >= ($upimgmax + 1)) {
                    // 中断 取消 大于  $upimgmax张图片的对象
                    uploader.removeFile(file, true);
                    $('.file-item').last().remove();
                }
            } else {
                $filePicker.show();
            }
        });
        // 文件上传过程中创建进度条实时显示。
        uploader.on('uploadProgress', function(file, percentage) {
            var $li = $('#' + file.id),
                $percent = $li.find('.progress span');
            // 避免重复创建
            if (!$percent.length) {
                $percent = $('<p class="progress"><span></span></p>').appendTo($li).find('span');
            }
            $percent.css('width', percentage * 100 + '%');
        });
        // 文件上传成功，给item添加成功class, 用样式标记上传成功。
        uploader.on('uploadSuccess', function(file, response) {
            console.log(response);
            var $li = $('#' + file.id),
                $img = $li.find('img'),
                $input = $li.find('input'), // 进行修改 -- 加一个隐藏域
                $fileUrl = response[0].url, // 图片上传地址
                $filename = file.name, // 上传文件名称
                $filesize = (file.size / 1024).toFixed(2); // 上传文件尺寸大小 保留2位
            $li.addClass('upload-state-done');
            // console.log(file);
            //console.log(response);
            // console.log('图片地址:' + $fileUrl);
            $input.val($fileUrl);
            $img.attr('src', $fileUrl);
            $img.attr('table', response[0].table);
            $img.attr("tableId",response[0].tableId);
            //bindRemovefiles(file); // 删除文件
            // 传值赋值
            // 商品详细图片 位置
            // 这里自己赋值 传值
        });

        // 文件上传失败，显示上传出错。
        uploader.on('uploadError', function(file) {
            var $li = $('#' + file.id),
                $error = $li.find('div.error');
            // 避免重复创建
            if (!$error.length) {
                $error = $('<div class="error"></div>').appendTo($li);
            }
            $error.text('上传失败');
        });
        // 完成上传完了，成功或者失败，先删除进度条。
        uploader.on('uploadComplete', function(file) {
            $('#' + file.id).find('.progress').remove();
            // console.log(file);
            // 获取文件统计信息。返回一个包含一下信息的对象。
            /*successNum 上传成功的文件数
             progressNum 上传中的文件数
             cancelNum 被删除的文件数
             invalidNum 无效的文件数
             uploadFailNum 上传失败的文件数
             queueNum 还在队列中的文件数
             interruptNum 被暂停的文件数
             */
            // console.log(uploader.getStats().uploadFailNum);
        });
        uploader.on('error', function(handler) {
            if (handler == "Q_EXCEED_NUM_LIMIT") {
                layer.msg("最多只能上传 " + $upimgmax + "张图片");
            }
        });
        // 删除按钮事件
        function bindRemovefiles(file) {
            $('.delimgbtns').click(function(file) {
                // 中断 取消 传图
                try{
                    uploader.removeFile(file, true);
                } catch (err) {

                }
                $upimgmax++;
                var spthisdiv = $(this).parent();
                spthisdiv.parent('.file-item').remove();
                $filePicker.show(); // 上传按钮显示
            });
        }
    }
    //    });
</script>
