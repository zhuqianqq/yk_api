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
        $room_id = $this->request->param("room_id", 0, "intval");
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
}
