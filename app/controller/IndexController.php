<?php
namespace app\controller;

use think\facade\View;
use think\facade\Db;

class IndexController extends BaseController
{
    public function index()
    {
        return "index";
        
		$list = Db::table('TOperator')->select()->toArray();

        return View::fetch("index",[
            "list" => $list
        ]);
    }

    public function hello()
    {
        $name = session('name');

        return 'hello,'.$name.'!';
    }
}
