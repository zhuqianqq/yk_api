<?php
// [ 前台入网认证入口文件 ]
namespace think;

require __DIR__ . '/../vendor/autoload.php';

$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
