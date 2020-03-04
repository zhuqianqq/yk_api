<?php
namespace app\controller;

use think\facade\Db;

class IndexController extends BaseController
{
    public function index()
    {
        return "index";
    }

    public function hello()
    {
        return 'hello';
    }
}
