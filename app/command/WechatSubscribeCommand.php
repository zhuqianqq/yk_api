<?php
namespace app\command;

use think\facade\Cache;
use think\facade\Db;
use app\model\TBoardcasterSubscribe;
use Hectorqin\ThinkWechat\Facade;
use app\util\WechatHelper;

class WechatSubscribeCommand extends BaseCommand
{
    /**
     * @var string 指令名称
     */
    protected $scriptName = "wechat_subscribe";

    /**
     * 执行入口(处理业务逻辑)
     */
    protected function _execute()
    {
        $time = time();
        while (true) {
            if (time() - $time >= 600) {
                $this->log('ten minite end!!!');
                die;
            }
            // 主播uid
            $boardcaster_uid = Cache::store('redis')->rpop('list:wechat:subscribe');
            if (empty($boardcaster_uid)) {
                sleep(2);
                continue;
            }
            $this->log("start boardcaster_uid：{$boardcaster_uid}");

            $room = Db::table('t_room')->where(['user_id' => $boardcaster_uid])->find();
            if (empty($room['user_id'])) {
                $this->log("room empty, boardcaster_uid：{$boardcaster_uid}");
                continue;
            }

            $subscribeInfo = Db::table('t_boardcaster_subscribe')->field('user_id')->where(['boardcaster_uid' => $boardcaster_uid, 'is_subscribe' => TBoardcasterSubscribe::IS_YES])->select();
            if (empty($subscribeInfo[0])) {
                $this->log("subscribe info empty, boardcaster_uid：{$boardcaster_uid}");
                continue;
            }
            $subscribeInfo = is_object($subscribeInfo) ? $subscribeInfo->toArray() : $subscribeInfo;
            $user_ids = array_column($subscribeInfo, 'user_id');
            $members = Db::table('t_member')->field('user_id,nick_name,openid')->where(['user_id' => ['in', implode(',', $user_ids)]])->select();
            if (empty($members[0])) {
                $this->log("members info empty, boardcaster_uid：{$boardcaster_uid}, user_ids: " . json_encode($user_ids));
                continue;
            }

            foreach ($members as $member) {
                if (empty($member['openid'])) {
                    $this->log("openid empty, boardcaster_uid：{$boardcaster_uid}, member: " . json_encode($member, JSON_UNESCAPED_UNICODE));
                    continue;
                }
                $this->sendMsg($boardcaster_uid, $member['openid'], $member['nick_name'], $room['title'], $room['create_time']);
            }

        }
    }

    protected function sendMsg($boardcaster_uid, $openid, $boardcaster_nickname, $room_title, $live_time, $room_name = '')
    {
        $room_name = !empty($room_name) ? $room_name : $room_title;
        $data = [
            'template_id' => 'FWlYtp96OHrSghGcLQd6TXpmzjVgHQKYbaYMkuk0q88', // 所需下发的订阅模板id
            'touser' => $openid,
            "miniprogram_state" => "trial",
            'page' => 'pages/room/room?user_id=' . $boardcaster_uid,
            'data' => [
                'thing1' => [
                    'value' => $boardcaster_nickname,
                ],
                'thing2' => [
                    'value' => $room_title,
                ],
                'date3' => [
                    'value' => $live_time,
                ],
                'thing6' => [
                    'value' => $room_name,
                ],
            ],
        ];

       /* $app = Facade::miniProgram();
        $r = $app->subscribe_message->send($data);*/
        $accessToken = WechatHelper::getAccessToken();
        $r = WechatHelper::sendMsg($data, $accessToken);
        $this->log('sendMsg : data: ' . json_encode($data, JSON_UNESCAPED_UNICODE) .' | result：' . json_encode($r));
        return $r;
    }
}