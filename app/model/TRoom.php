<?php
namespace app\model;

use app\util\Tools;
use think\facade\Db;

class TRoom extends BaseModel
{
    protected $table = "t_room";

    public static function getList($page,$page_size){
        $page = $page ? $page : 1;
        $query = Db::table("t_room r")
            ->leftJoin("t_member m", "r.user_id=m.user_id ")
            ->field("r.*,m.nick_name,m.avatar,m.display_code");
        $list=$query->page($page, $page_size)->select();
        $total = $query->count();
        $has_next=0;
        self::checkHasNextPage($list,$page_size,$has_next);
        $data = [
            "list" => $list,
            "current_page" => $page,
            "total" => $total,
            "has_next" => $has_next, //是否有下一页
        ];
        return $data;
    }


    /**
     * 下播
     * @param $room_id
     * @param $user_id
     */
    public static function closeRoom($room_id,$user_id)
    {
        $data = Db::table("t_room")->where(["room_id" => $room_id])->find();
        if (empty($data)) {
            return Tools::outJson(100, "room_id未开播");
        }
        if($data["user_id"] != $user_id){
            return Tools::outJson(100, "你无权关闭该直播");
        }
        $data = $data->toArray();
        unset($data["id"]);

        Db::startTrans();
        Db::table("t_room")->where(["room_id" => $room_id,"user_id" => $user_id])->delete();
        Db::table("t_room_history")->insert($data);
        Db::commit();

        return Tools::outJson(0, "下播成功");
    }
}
