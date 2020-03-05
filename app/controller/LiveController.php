<?php
/**
 * 直播
 */

namespace app\controller;

use app\model\TRoom;
use app\util\Tools;
use think\facade\Db;

class LiveController extends BaseController
{
    protected $middleware = [
        'access_check' => ['only' => ['']],
    ];

    /**
     * 直播列表
     */
    public function getList()
    {
        $page_size = $this->request->param("page_size", 10, "intval");

        $list = Db::name('t_room')->order('id', 'desc')->paginate($page_size);

        return $this->outJson(0, "success", $list);
    }

    /**
     * 直播详情
     */
    public function getInfo()
    {
        $room_id = $this->request->param("room_id");
        $user_id = $this->request->param("user_id", 0, "intval");

        if ($room_id > 0) {
            $where = ['room_id' => $room_id];
        }elseif ($user_id > 0) {
            $where = ['user_id' => $user_id];
        }else{
            return $this->outJson(100, "user_id或room_id无效");
        }

        $data = TRoom::where($where)->find();

        return $this->outJson(0, "success", $data);
    }

    public function addRoom()
    {
        $user_id = $this->request->param("user_id", 1, "intval");
        $room_id = $this->request->param("room_id");
        $title = $this->request->param("title");
        $frontcover = $this->request->param("frontcover");
        $location = $this->request->param("location");
        $push_url = $this->request->param("push_url");
        $show_product = $this->request->param("show_product");
        $room = new TRoom();
        $room->user_id = $room_id;
        $room->title = $title;
        $room->frontcover = $frontcover;
        $room->location = $location;
        $room->push_url = $push_url;
        $room->show_product = $show_product;
        $room->save();
        return $this->outJson(0, "保存成功！", $room);
    }

}
