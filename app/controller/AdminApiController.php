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
     * 禁推直播流
     */
    public function forbidLive()
    {
        $room_id = $this->request->param("room_id", '', "trim");
        $oper_user = $this->request->param("oper_user", '', "trim");
        $forbid_day = $this->request->param("forbid_day", '1', "intval"); //禁播天数
        $reason = $this->request->param("reason", '', "trim");

        if (empty($room_id)) {
            return $this->outJson(100, "room_id参数不能为空");
        }
        if (empty($oper_user)) {
            return $this->outJson(100, "操作用户参数不能为空");
        }
        if(empty($reason)){
            return $this->outJson(100, "禁播原因不能为空");
        }

        $room = TRoom::where("room_id",$room_id)->field('push_url,user_id')->find();
        if(empty($room)){
            return $this->outJson(100, "直播不存在或未开播");
        }

        $tenService = new TenCloudLiveService();
        list($domain,$app_name,$stream_name) = TenCloudLiveService::parsePushUrl($room->push_url);
        $end_time = time() + $forbid_day * 24 * 3600;
        $resume_time = Tools::getUtcTime($end_time);

        $result = $tenService->forbidLiveStream($stream_name,$app_name,$domain,$resume_time,$reason);

        if($result["code"] == 0){
            //
            $res = TRoom::closeRoom($room_id, $room->user_id,$oper_user);
            $this->log("forbid_live res:".json_encode($res,JSON_UNESCAPED_UNICODE),$this->request->getInput());
            return json($res);
        }else{
            return json($result);
        }
    }

    /**
     * 恢复直播流
     */
    public function resumeLive()
    {
        $room_id = $this->request->param("room_id", '', "trim");
        $oper_user = $this->request->param("oper_user", '', "trim");
        $user_id = $this->request->param("user_id", '', "intval");

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

        $result = $tenService->resumeLiveStream($stream_name,$app_name,$domain);

        if($result["code"] == 0){
            return json($result);
        }else{
            return json($result);
        }
    }
}
