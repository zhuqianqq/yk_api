<?php
/**
 * Created by PhpStorm.
 * User: Justin
 * Date: 2020/3/12
 * Time: 11:50
 */

namespace app\controller;

use app\model\TBoardcasterSubscribe;
use app\model\TMember;
use think\facade\Db;

class WechatController extends BaseController
{
    protected $middleware = [
        'access_check' => ['only' => ['subscribe', 'getSubscribeInfo']],
    ];

    /**
     * 关注&订阅
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function subscribe()
    {
        $boardcaster_uid = $this->request->post("boardcaster_uid", 0, "intval");
        $is_follow = $this->request->post("is_follow", -1, "intval");
        $is_subscribe = $this->request->post("is_subscribe", -1, "intval");

        if (empty($boardcaster_uid)) {
            return $this->outJson(100, "参数错误");
        }

        if ($boardcaster_uid == $this->user_id) {
            return $this->outJson(100, "自己不能关注自己");
        }

        $values = array_merge([-1], array_keys(TBoardcasterSubscribe::$isLabels));
        if (!in_array($is_follow, $values) || !in_array($is_subscribe, $values)) {
            return $this->outJson(100, "参数错误");
        }
        if ($is_follow == -1 && $is_subscribe == -1) {
            return $this->outJson(100, "没有任何操作");
        }

        $member = TMember::where(['user_id' => $boardcaster_uid, 'is_broadcaster' => TMember::IS_BROADCASTER_YES])->find();
        if (empty($member->user_id)) {
            return $this->outJson(100, "查询不到该主播");
        }

        $now_time = date('Y-m-d H:i:s');
        $sql = 'insert into t_boardcaster_subscribe (';
        $updates = [
            'boardcaster_uid' => $boardcaster_uid,
            'user_id' => $this->user_id,
            'create_time' => "'{$now_time}'",
            'update_time' => "'{$now_time}'",
        ];
        $extra_sql = '';
        if ($is_follow != -1) {
            $updates['is_follow'] = $is_follow;
            $extra_sql .= ',is_follow=VALUES(is_follow)';
        }
        if ($is_subscribe != -1) {
            $updates['is_subscribe'] = $is_subscribe;
            $extra_sql .= ',is_subscribe=VALUES(is_subscribe)';
        }
        $sql .= implode(',', array_keys($updates)) . ') values (' . implode(',', array_values($updates)) . ') ON DUPLICATE KEY UPDATE update_time=VALUES(update_time)' . $extra_sql;
        $result = Db::execute($sql);
        if ($result) {
            return $this->successJson();
        } else {
            return $this->failJson(500, '系统错误');
        }

    }

    /**
     * 获取主播关注订阅信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getSubscribeInfo()
    {
        $boardcaster_uid = $this->request->param("boardcaster_uid", 0, "intval");
        if (empty($boardcaster_uid)) {
            return $this->outJson(100, "参数错误");
        }

        $result = Db::table('t_boardcaster_subscribe')->where(['user_id' => $this->user_id, 'boardcaster_uid' => $boardcaster_uid])->find();
        if (empty($result)) {
            $result["boardcaster_uid"] = $boardcaster_uid;
            $result["user_id"] = $this->user_id;
            $result["is_follow"] = 0;
            $result["is_subscribe"] = 0;
        }
        return $this->successJson($result);
    }
}