<?php
/**
 * 管理后台相关接口调用
 */

namespace app\controller;

use app\model\TRoom;
use app\service\TenCloudLiveService;
use app\util\Tools;

class AdminApiController extends BaseController
{
    protected $middleware = [
        'admin_check' => ['except' => ['closeRoom']],
    ];

    protected $logfile = 'admin_api';

    /**
     * 后台关播
     */
    public function closeRoom()
    {
        $room_id = $this->request->param("room_id", '', "trim");
        $oper_user = $this->request->param("oper_user", '', "trim");

        if (empty($room_id)) {
            return $this->outJson(100, "room_id参数不能为空");
        }
        if (empty($oper_user)) {
            return $this->outJson(100, "操作用户参数不能为空");
        }

        $room = TRoom::where("room_id",$room_id)->field('push_url,user_id')->find();
        if(empty($room)){
            return $this->outJson(100, "直播不存在或未开播");
        }

        $tenService = new TenCloudLiveService();
        list($domain,$app_name,$stream_name) = TenCloudLiveService::parsePushUrl($room->push_url);
        $result = $tenService->dropLiveStream($stream_name,$app_name,$domain);

        if($result["code"] == 0){
            $res = TRoom::closeRoom($room_id, $room->user_id,$oper_user);
            $this->log("close_room room_id:{$room_id},oper_user:{$oper_user},res:".json_encode($res,JSON_UNESCAPED_UNICODE));
            return json($res);
        }else{
            return json($result);
        }
    }
}
