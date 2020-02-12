<style>
    body, html, #container {
        width: 100%;
        margin: 0px
    }

    #tip {
        background-color: #ddf;
        color: #333;
        border: 1px solid silver;
        box-shadow: 3px 4px 3px 0px silver;
        position: absolute;
        top: 10px;
        right: 10px;
        border-radius: 5px;
        overflow: hidden;
        line-height: 20px;
    }

    #tip input[type="text"] {
        height: 25px;
        border: 0;
        padding-left: 5px;
        width: 200px;
        border-radius: 3px;
        outline: none;
    }
</style>
<!--高德基础js-->
<script src="/packages/layui/layui.all.js"></script>
<script type="text/javascript" src="http://webapi.amap.com/maps?v=1.3&key=f7f4f5f5cca0c860feb9ae2f65f9c310&plugin=AMap.Autocomplete,AMap.PlaceSearch"></script>
<script src="http://webapi.amap.com/ui/1.0/main.js?v=1.0.11"></script>
<script src="/packages/yuntu/house.js"></script>
<script type="text/javascript" src="https://webapi.amap.com/demos/js/liteToolbar.js"></script>

<div class="block-flat">
    <div class="form-group">
        <div class="col-sm-12">
            <!--高德地图-->
            <div id="container" class="map" tabindex="0" style="height: 500px;"></div>
            <div id="tip">
                <input type="text" id="keyword" name="keyword" value="请搜索地址" onfocus='this.value=""'/>
            </div>
        </div>
    </div>
</div>




