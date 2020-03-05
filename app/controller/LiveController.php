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
     * 用户商品列表
     */
    public function getLiveInfo()
    {
        $room_id = $this->request->param("room_id", 0, "intval");

        if ($room_id <= 0) {
            return $this->outJson(100, "room_id无效");
        }

        $data = TRoom::where('room_id', $room_id)->find();

        return $this->outJson(0, "success", $data);
    }
}
