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
        $name = session('name');

        return 'hello,'.$name.'!';
    }
}
