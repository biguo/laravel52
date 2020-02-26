


<script data-exec-on-popstate>

    $(function () {

        $('.pull-right button').attr('type','button');

        $('.pull-right button').click(function () {

            layer.msg('请选择电器');
            // let btn = $(this).button('loading');
            // setTimeout(function () {
            //     btn.button('reset');
            // }, 3000);
            // console.log(checkimg());
            // $('form').submit();
        })
    });


    function checkimg() {
        return $('.thumbnail').size()
    }
</script>