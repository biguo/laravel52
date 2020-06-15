<?php


//验证码类型
define('SmsCodeType_LOGIN',0);//登陆
define('SmsCodeType_REGISTER',1);//注册

define('Status_Online',1);//产品 在线
define('Status_Offline',0);//下线

define('Status_UnPay',1);//订单 未付款
define('Status_Payed',2);//已付款
define('Status_Canceled',4);//已取消
define('Status_Refund',5);//已退款
define('Status_OrderUsed',6);//已使用

define('Status_UnUse',0);//代金券 未使用
define('Status_Used',1);//已使用

define('Status_Online_video',1);//上线
define('Status_Review_video',2);//审核中
define('Status_Offline_video',3);//下线
define('Status_Reject_video',4);//驳回


define('Change_Recharge',1);//记录 充值
define('Change_Consume',2);//消费

define('Upload_Domain','http://upload.binghuozhijia.com/');//七牛
define('Default_Icon','http://upload.binghuozhijia.com/image/pt.jpg');//默认等级
define('Default_Pic','http://bhzj.binghuozhijia.com/test/bhzj/public/sample/default_pic.png');//默认头像
define('wanted','http://upload.binghuozhijia.com/uploads/5e71b0619c92e/5e71b0619c8d5.jpg');

define('Youth',27);//青年券产品的id  随着数据库的改变 可能会变






