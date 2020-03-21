<?php

namespace app\model;

use app\util\Tools;
use think\facade\Config;
use think\facade\Db;

class TRoom extends BaseModel
{
    protected $table = "t_room";

    public static function getList($page, $page_size,$user_id=0)
    {
        $page = $page ? $page : 1;
        $query = Db::table("t_room r")
            ->leftJoin("t_member m", "r.user_id=m.user_id ")
            ->field("r.*,m.nick_name,m.avatar,m.display_code");
        if ($user_id > 0) {
            $query = Db::table("t_room r")
                ->leftJoin("t_member m", "r.user_id=m.user_id ")->where("m.user_id<>" . $user_id)
                ->field("r.*,m.nick_name,m.avatar,m.display_code");
        }
        $list = $query->order("like_count", 'desc')->page($page, $page_size)->select();
        $total = $query->count();
        $has_next = 0;

        self::checkHasNextPage($list, $page_size, $has_next);

        $data = [
            "list" => $list,
            "current_page" => $page,
            "total" => $total,
            "has_next" => $has_next, //是否有下一页
        ];

        return $data;
    }


    /**
     * 用户主动下播
     * @param string $room_id
     * @param int $user_id
     * @param $oper_user 操作用户
     */
    public static function closeRoom($room_id, $user_id, $oper_user = '')
    {
        $data = Db::table("t_room")->where(["room_id" => $room_id])->find();
        if (empty($data)) {
            return Tools::outJson(100, "room_id未开播");
        }
        if ($data["user_id"] != $user_id) {
            return Tools::outJson(100, "你无权关闭该直播");
        }
        Db::startTrans();
        Db::table("t_room")->where(["room_id" => $room_id, "user_id" => $user_id])->delete();
        $data['oper_user'] = !empty($oper_user) ? $oper_user : $user_id; //关播用户
        self::insertRoomHistory($data);
        Db::commit();

        return Tools::outJson(0, "下播成功");
    }

    /**
     * 断流事件系统自动断播
     * @param string $room_id
     * @param string $sequence 消息序列号，标识一次推流活动，一次推流活动会产生相同序列号的推流和断流消息
     * @param string $oper_user 操作用户
     * @return array
     */
    public static function closeRoomBySystem($room_id,$sequence,$oper_user = 'system')
    {
        $data = Db::table("t_room")->where(["room_id" => $room_id,'sequence' => $sequence])->find();
        if (empty($data)) {
            return Tools::outJson(100, "未找到开播数据");
        }

        Db::startTrans();
        Db::table("t_room")->where(["room_id" => $room_id, "sequence" => $sequence])->delete();
        $data['oper_user'] = $oper_user;
        self::insertRoomHistory($data);
        Db::commit();

        return Tools::outJson(0, "下播成功");
    }

    /**
     * @param $data
     * @return int|string
     */
    private static function insertRoomHistory(&$data)
    {
        $insert_data = [
            "room_id" => $data['room_id'],
            "user_id" => $data['user_id'],
            "title" => $data['title'],
            "frontcover" => $data['frontcover'],
            "location" => $data['location'],
            "create_time" => $data['create_time'], //开播时间
            "push_url" => $data['push_url'],
            "show_product" => $data['show_product'],
            "mixed_play_url" => $data['mixed_play_url'],
            "oper_user" =>  $data['oper_user'], //关播用户
            "like_count" => $data['like_count'],
            "view_count" => $data['view_count'],
            "close_time" => date("Y-m-d H:i:s"), //关播时间
        ];

        return Db::table("t_room_history")->insertGetId($insert_data);
    }

    /**
     * 生成推流和拉流地址
     * @param int $display_code 用户display_code
     * @return string
     */
    public static function generatePushAndPUllUrl($user_id)
    {
        $display_code = TMember::generateDisplayCode($user_id);
        $live_config = Config::get("tencent_cloud");
        $time = time();
        $stream_name = $display_code . "_" . $time;

        $txTime = strtoupper(base_convert(time() + 24 * 3600, 10, 16)); //24小时后过期
        $txSecret = md5($live_config["callback_key"] . $stream_name . $txTime);

        $push_url = $live_config['push_domain'] . "/live/{$stream_name}?txSecret={$txSecret}&txTime={$txTime}";
        $pull_url = $live_config['pull_domain'] . "/live/{$stream_name}.flv";

        return [$push_url,$pull_url];
    }
}
