<?php
namespace app\controller;

use app\model\TPrebroadcast;
use think\facade\Db;

class PrebroadcastController extends BaseController
{
    public function add()
    {
        $user_id = $this->request->post("user_id",0,"intval");
        $title = $this->request->post("title");
        $fontcover = $this->request->post("fontcover");
        $playtime = $this->request->post("playtime");
        $nowTime = time();
        $time = date('Y-m-d H:i:s', $nowTime);
        $item = new TPrebroadcast();
        $item->title = $title;
        $item->user_id = $user_id;
        $item->fontcover = $fontcover;
        $item->playtime = $playtime;
        $item->createtime = $time;
        $item->save();
        return $this->outJson(0, "保存成功！");
    }

    public function listPrebroadcast()
    {
        $user_id = $this->request->post("user_id",0,"intval");
        if($user_id<=0)
        {
            return $this->outJson(1, "参数错误！");
        }
        $items = TPrebroadcast::where(" user_id = ".$user_id." and playtime>now() ")->order("createtime asc")->select();
        return $this->outJson(0, "查询成功！", $items);
    }

    public function removePrebroadcast()
    {
        $id = $this->request->post("id",0,"intval");
        if($id<=0)
        {
            return $this->outJson(1, "参数错误！");
        }
        TPrebroadcast::destroy($id);
        return $this->outJson(0, "删除成功！");
    }

}
