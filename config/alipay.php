<?php
/**
 * 支付宝配置
 */
return [
    'partner' => '2088721010680696',// 合作者身份ID
    'seller_id' => 'payment@hanyouhui.com',//卖家支付宝账号
    'app_id' => '2017051807279851', //应用appid
    'private_key_path' => 'alipay_key/rsa_private_key.pem',    // 商户的私钥
    'ali_public_key_path' => 'alipay_key/alipay_public_key.pem', // 支付宝公钥
    'sign_type' => 'RSA2',    // 签名方式
    'input_charset' => "UTF-8",    // 字符编码格式 目前支持 gbk 或 utf-8
    'cacert' => 'cacert.pem',    // ca证书路径地址，用于curl中ssl校验,请保证cacert.pem文件在当前文件夹目录中
    'transport' => "http",  // 访问模式
    'notify_url' => env('APP_URL') . '/pay/alipayNotify',
];
