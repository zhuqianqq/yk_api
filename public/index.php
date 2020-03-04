<?php
// 入口文件
namespace think;

require __DIR__ . '/../vendor/autoload.php';

$http = (new App())->http;

define('APP_ENV', \think\facade\Env::get("APP_ENV","test")); //应用环境 prod(生产),test(测试)

$response = $http->run();
$response->send();

$http->end($response);
