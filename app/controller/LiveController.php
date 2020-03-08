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
use think\Model;

class LiveController extends BaseController
{
    protected $middleware = [
        'access_check' => ['only' => ['closeRoom','addRoom']],
    ];

    /**
     * 直播列表
     */
    public function getList()
    {
        $page_size = $this->request->param("page_size", 10, "intval");
        $page = $this->request->param("page", 1, "intval");
        $page = $page ? $page : 1;
        $data = TRoom::getList($page,$page_size);
        return $this->outJson(0, "success", $data);
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
        $user = TMember::where("user_id",$data["user_id"])->find();
        if($user!=null){
            $data["display_code"]=$user->display_code;
            $data["nick_name"]=$user->nick_name;
            $data["avatar"]=$user->avatar;
        }else{
            $data["display_code"]="";
            $data["nick_name"]="";
            $data["avatar"]="";
        }
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
        //$room = new TRoom();
        $room = TRoom::where(["user_id"=>$user_id])->find();
        if($room==null) {
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
        }else {
            TRoom::where([
                "user_id" => $user_id,
            ])->update([
                'title' => $title,
                'frontcover' => $frontcover,
                'location' => $location,
                'push_url' => $push_url,
                'mixed_play_url' => $mixed_play_url,
                'show_product' => $show_product
            ]);
        }
        $room = TRoom::where(["user_id"=>$user_id])->find();
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
     * 直播关播
     */
    public function closeRoom()
    {
        $room_id = $this->request->param("room_id", '', "trim");

        if (empty($room_id)) {
            return $this->outJson(100, "room_id不能为空");
        }

        return json(TRoom::closeRoom($room_id,$this->user_id,$this->user_id));
    }
}
