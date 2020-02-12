$(document).ready(function() {

    //高德地图api
    var windowsArr = [];
    var marker = [];
    var lat = ($("#lati").val()=='')?'39.90923':$("#lati").val();
    var lng = ($("#longi").val()=='')?'116.397428':$("#longi").val();
    var map = new AMap.Map("container", {
        resizeEnable: true,
        center: [lng,lat],//地图中心点
        zoom: 13,//地图显示的缩放级别
        keyboardEnable: false
    });
    //插件
    AMap.plugin(['AMap.Geocoder','AMap.Autocomplete','AMap.PlaceSearch'],function(){
        //逆地理编码
        var geocoder = new AMap.Geocoder({
            city: "010"//城市，默认：“全国”
        });
        var marker = new AMap.Marker({
            map:map,
            bubble:true,
        })
        map.on('click',function(e){
            marker.setPosition(e.lnglat);
            geocoder.getAddress(e.lnglat,function(status,result){
                if(status=='complete'){
                    document.getElementById('address').value = result.regeocode.formattedAddress
                    //document.getElementById('input').value = result.regeocode.formattedAddress
                    //地理编码
                    geocoder.getLocation(result.regeocode.formattedAddress, function(status, res) {
                        if (status === 'complete' && res.info === 'OK') {
                            layer.msg('定位成功');
                            // console.log(result)
                            document.getElementById('lati').value = result.regeocode.addressComponent.businessAreas[0].location.lat
                            document.getElementById('longi').value = result.regeocode.addressComponent.businessAreas[0].location.lng
                            //
                            // document.getElementById('lat').value = result.geocodes[0].location.lat
                            // document.getElementById('lng').value = result.geocodes[0].location.lng
                        }else{
                            //获取经纬度失败
                            layer.msg('请再试一次或手动填写经纬度');
                        }
                    });
                }
                else
                {
                    layer.msg('定位失败');
                }
            })
        })
        //搜索地址
        var autoOptions = {
            city: "北京", //城市，默认全国
            input: "keyword"//使用联想输入的input的id
        };
        autocomplete= new AMap.Autocomplete(autoOptions);
        var placeSearch = new AMap.PlaceSearch({
            city:'北京',
            map:map
        })
        AMap.event.addListener(autocomplete, "select", function(e){
            //TODO 针对选中的poi实现自己的功能
            //placeSearch.setCity(e.poi.adcode);
            placeSearch.search(e.poi.name)
            document.getElementById('address').value = e.poi.name
            document.getElementById('lati').value = e.poi.location.lat
            document.getElementById('longi').value = e.poi.location.lng
            layer.msg('搜索成功');
        });
    });

})