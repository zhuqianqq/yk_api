<?php
/**
 * 直播
 */

namespace app\controller;

use app\model\TMember;
use app\model\TPrebroadcast;
use app\model\TRoom;
use app\model\TRoomHistory;
use app\util\Tools;
use think\facade\Config;
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
        } elseif ($user_id > 0) {
            $where = ['user_id' => $user_id];
        } else {
            return $this->outJson(100, "user_id或room_id无效");
        }

        $data = TRoom::where($where)->find();

        return $this->outJson(0, "success", $data);
    }

    public function addRoom()
    {
        $im_config = Config::get('im');
        $user_id = $this->request->param("user_id", 0, "intval");
        $user = TMember::where(["user_id"=>$user_id])->find();

        $room_id = $this->request->param("room_id");
        $title = $this->request->param("title");
        $frontcover = $this->request->param("frontcover");
        $location = $this->request->param("location");
        $push_url = $this->request->param("push_url");
        //$mixed_play_url = $this->request->param("mixed_play_url");
        $mixed_play_url = "http://live.laotouge.cn/live/".$im_config["IM_SDKAPPID"]."_".$user->display_code.".flv";
        $show_product = $this->request->param("show_product");
        $prebroadcast_id = $this->request->param("prebroadcast_id",0, "intval");
        $room = new TRoom();
        $room->user_id = $user_id;
        $room->room_id = $room_id;
        $room->title = $title;
        $room->frontcover = $frontcover;
        $room->location = $location;
        $room->push_url = $push_url;
        $room->mixed_play_url = $mixed_play_url;
        $room->show_product = $show_product;
        $room->save();
        if($prebroadcast_id>0) {
            TPrebroadcast::where([
                "id" => $prebroadcast_id,
            ])->update([
                'status' => 1
            ]);
        }
        return $this->outJson(0, "开播成功！", $room);
    }

    /**
     * 关播
     */
    public function closeRoom()
    {
        $room_id = $this->request->param("room_id", '', "trim");

        if (empty($room_id)) {
            return $this->outJson(100, "room_id不能为空");
        }

        $data = TRoom::where(["room_id" => $room_id])->find();
        if (empty($data)) {
            return $this->outJson(100, "room_id未开播");
        }
        if($data->user_id != $this->user_id){
            return $this->outJson(100, "你无权关闭该直播");
        }
        $data = $data->toArray();
        unset($data["id"]);

        Db::startTrans();
        Db::table("t_room")->where(["room_id" => $room_id,"user_id" => $this->user_id])->delete();
        Db::table("t_room_history")->insert($data);
        Db::commit();

        return $this->outJson(0, "下播成功");
    }
}
