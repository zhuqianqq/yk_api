<?php
namespace app\controller;

use app\model\TPrebroadcast;
use think\facade\Db;

class PrebroadcastController extends BaseController
{
    public function index()
    {
        return "index";
    }

    public function Add($title,$userid,$fontcover,$playtime)
    {
        $nowTime = time();
        $time = date('Y-m-d H:i:s', $nowTime);
        $item = new TPrebroadcast();
        $item->title = $title;
        $item->userid = $userid;
        $item->fontcover = $fontcover;
        $item->playtime = $playtime;
        $item->createtime = $time;
        $item->save();
        return $this->outJson(0, "保存成功！", "");
    }

    public function ListPrebroadcast($userid)
    {
        $items = TPrebroadcast::where("userid", $userid)->order("createtime asc");
        return $this->outJson(0, "查询成功！", $items);
    }

}
