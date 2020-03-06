<?php
namespace app\controller;

use app\model\TPrebroadcast;
use think\facade\Db;
use app\model\TRoom;

class PrebroadcastController extends BaseController
{
    /**
     * 创建预播
     * @return array
     */
    public function add()
    {
        $user_id = $this->request->post("user_id",0,"intval");
        $title = $this->request->post("title");
        $fontcover = $this->request->post("fontcover");
        $playtime = $this->request->post("playtime");
        $show_product = $this->request->post("show_product",0,"intval");

        $nowTime = time();
        $time = date('Y-m-d H:i:s', $nowTime);
        $item = new TPrebroadcast();
        $item->title = $title;
        $item->user_id = $user_id;
        $item->fontcover = $fontcover;
        $item->playtime = $playtime;
        $item->createtime = $time;
        $item->show_product = $show_product;
        $item->save();
        return $this->outJson(0, "保存成功！",$item);
    }

    /**
     * 预播列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function listPrebroadcast()
    {
        $user_id = $this->request->post("user_id", 0, "intval");
        if ($user_id <= 0) {
            return $this->outJson(1, "参数错误！");
        }
        $items = TPrebroadcast::where([
            ['user_id', '=', $user_id],
           // ['playtime', '>=', date('Y-m-d H:i:s',time())],
            ['status', '=', 0]
        ])->order("playtime asc")->select();
        return $this->outJson(0, "查询成功！", $items);
    }

    /**
     * 删除预播
     * @return array
     * @throws \Exception
     */
    public function removePrebroadcast()
    {
        $id = $this->request->post("id",0,"intval");
        $user_id = $this->request->post("user_id",0,"intval");
        if($id<=0)
        {
            return $this->outJson(1, "参数错误！");
        }
        TPrebroadcast::where(" id = ".$id." and user_id=".$user_id)->delete();
        return $this->outJson(0, "删除成功！");
    }

    /**
     * 详情
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function prebroadcastDetail()
    {
        $id = $this->request->get("id",0,"intval");
        if($id<=0)
        {
            return $this->outJson(1, "参数错误！");
        }
        $prebroadcast =  TPrebroadcast::find($id);
        if($prebroadcast==null){
            return $this->outJson(1, "查找预播失败！");
        }

        $live_count = TRoom::where(['user_id'=>$prebroadcast['user_id']])->count();
        $prebroadcast['is_live'] = $live_count ? 1 : 0;

        return $this->outJson(0, "查找成功！",$prebroadcast);
    }
}
