<?php
/**
 * 管理后台相关接口调用
 */

namespace app\controller;

use app\model\TMember;
use app\model\TRoom;
use app\service\TenCloudLiveService;
use app\util\Tools;
use think\facade\Config;
use app\model\TRoomOperLog;

class AdminApiController extends BaseController
{
    protected $middleware = [
        'admin_check' => ['only' => ['forbidLive','resumeLive']],
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
        $end_time = time() + $forbid_day * 24 * 3600;

//        list($domain,$app_name,$stream_name) = TenCloudLiveService::parsePushUrl($room->push_url);
//        $resume_time = Tools::getUtcTime($end_time);
//
//        $result = $tenService->forbidLiveStream($stream_name,$app_name,$domain,$resume_time,$reason);

        $result = $tenService->sendGroupMsg($room_id,"close_room|您的直播间因违规，暂时关闭，如有疑问请联系客服"); //发送下播消息

        if($result["code"] === 0){
            $res = TRoom::closeRoom($room_id, $room->user_id,$oper_user);
            $this->log("forbid_live res:".json_encode($res,JSON_UNESCAPED_UNICODE),$this->request->getInput());
            TMember::forbidMember($room->user_id,$room->room_id,$end_time,$oper_user);
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
        $user_id = $this->request->param("user_id", '', "intval"); //要恢复的主播user_id
        $oper_user = $this->request->param("oper_user", '', "trim");
        $reason = $this->request->param("reason", '', "trim");

        if (empty($user_id)) {
            return $this->outJson(100, "user_id参数不能为空");
        }

        $user = TMember::where("user_id",$user_id)->field('user_id,display_code')->find();
        if(empty($user)){
            return $this->outJson(100, "主播用户不存在");
        }

        $ten_config = Config::get("tencent_cloud");
        $stream_name = $ten_config['IM_SDKAPPID']."_".$user["display_code"];
        $tenService = new TenCloudLiveService();

        $result = $tenService->resumeLiveStream($stream_name,"live");

        $this->log("resume_live res:".json_encode($result,JSON_UNESCAPED_UNICODE),$this->request->getInput());

        if($result["code"] === 0){
            TMember::unforbidMember($user->user_id,$oper_user);
            return json($result);
        }else{
            return json($result);
        }
    }

    public function test()
    {
        $tenService = new TenCloudLiveService();
        //$ret = $tenService->sendGroupSystemNotification("room_101057","close_room");

        $ret = $tenService->sendGroupMsg("room_101057","close_room|您的直播间因违规，暂时关闭，如有疑问请联系客服");

        return json($ret);
    }
}
