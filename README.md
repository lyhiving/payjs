# payjs

本项目是为PayJS的composer适配的，可以为你的项目接入微信支付功能。

PAYJS 旨在解决需要使用交易数据流的个人、创业者、个体户等小微支付需求，帮助开发者使想法快速转变为原型。各种懂你的~


如果你想使用本项目，请使用 composer 安装

```$xslt
$ composer require lyhiving/payjs
```
或者在你的项目跟目录编辑 ```composer.json```
```$xslt
"require": {
    "lyhiving/payjs": "^1.1"
}
```
更新
```$xslt
$ composer update
```


```$xslt
<?php
require 'vendor/autoload.php';

use \Payjs\Payjs;

$payjs = new Payjs([
    //jspay商户号id
    'AK' => '',
    //jspay商户密钥
    'SK' => ''
]);

//订单id
$orderid = 'OID_' . time();  
//订单金额，单位是分，也就是说101为收1.01RMB  
$total_fee = 101;  
//商品说明  
$body = '测试订单';  

//增加回传数据，适合支付，不适用查询
$payjs = $payjs->attach($attach);  

//如果想指定回调地址
$payjs = $payjs->notify($notifyurl);  

//扫码支付  
$r1 = $payjs->qrpay($orderid,$total_fee,$body);  
print_r($r1);  

//收银台模式
$gourl ='';//暂时不支持跳转，如果有填写会出错。以后支持即为支付后跳转
$r2 = $payjs->cashier($orderid, $total_fee, $body, $gourl);
print_r($r2);


//查询订单
$r3= $payjs->Query($orderid);
print_r($r3);
```

传入参数说明

| 变量名 | 类型 | 必填 | 说明 |
| :----- |:------| :-- | :-----------|
| $orderid | string(32) | Y | 订单id；不填写默认使用时间戳+随机六位数字(仅限测试) |
| $totle_fee | int(16) | Y | 订单金额；单位（分）如果不填写默认为￥0.01 |
| $body | string(32) | N | 商品说明；如果不填写默认为“订单” |



#License  
payjs is under the MIT license.
