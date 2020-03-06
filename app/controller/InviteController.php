<?php
/**
 * 邀请页相关接口
 */

namespace app\controller;

use think\facade\Db;
use app\model\TInviteProduct;
use app\model\TMember;
use app\model\TInviteOrder;
use app\model\TInviteRelation;
use app\util\Tools;

class InviteController extends BaseController
{
    protected $middleware = [
        'access_check' => ['only' => ['getRewardInfo','getInviteInfo','createOrder','isBroadcaster','payCallback']],
    ];

    /**
     * 展示奖金信息
     */
    public function getRewardInfo()
    {
        $today_time = date('Y-m-d 00:00:00');
        $state = TInviteOrder::STATE_PAYED;
        $orders = TInviteOrder::where(" inviter_uid = {$this->user_id} and update_time >= '{$today_time}' and state = {$state} ")->field('sum(reward_amount) as reward_amount')->find()->toArray();
        $today_reward = !empty($orders['reward_amount']) ? floatval($orders['reward_amount']) : 0;
        $orders = TInviteOrder::where(" inviter_uid = {$this->user_id} and state = {$state} ")->field('sum(reward_amount) as reward_amount')->find()->toArray();
        $total_reward = !empty($orders['reward_amount']) ? floatval($orders['reward_amount']) : 0;

        return $this->outJson(0, "成功", [
            'today_reward' => $today_reward,
            'total_reward' => $total_reward,
        ]);
    }

    /**
     * 展示邀请信息
     */
    public function getInviteInfo()
    {
        $state = $this->request->param("state", -1, "intval");
        $page = $this->request->param("page", 1, "intval");
        $page_size = $this->request->param("page_size", 10, "intval");
        if (!in_array($state, array_merge(array_keys(TInviteOrder::$stateLabels), [-1]))) {
            return $this->outJson(100, "参数错误");
        }

        $where = ['inviter_uid' => $this->user_id];
        if ($state != -1) {
            $where['state'] = $state;
        }

        $query = TInviteOrder::where($where);
        $total = $query->count();
        $list = $query->order('id', 'desc')->limit(($page - 1) * $page_size, $page_size)->select()->toArray();

        $total_count = TInviteOrder::where(['inviter_uid' => $this->user_id])->count();
        $pay_count = TInviteOrder::where(['inviter_uid' => $this->user_id, 'state' => TInviteOrder::STATE_PAYED])->count();
        $unpay_count = $total_count - $pay_count;

        // 查找我的邀请人
        $inviterInfo = TInviteRelation::getInviterByUid($this->user_id);

        return $this->outJson(0, "成功", [
            'list' => $list,
            'agg' => [
                'total_count' => $total_count,
                'pay_count' => $pay_count,
                'unpay_count' => $unpay_count,
            ],
            'my_inviter' => $inviterInfo,
            'total' => $total
        ]);
    }

    /**
     * 展示开播产品信息
     */
    public function getInviteProduct()
    {
        $product = TInviteProduct::where('is_del', TInviteProduct::IS_DEL_NO)->find()->toArray();
        return !empty($product) ? $this->outJson(0, "成功", $product) : $this->outJson(500, "查询失败");
    }

    /**
     * 创建邀请订单
     */
    public function createOrder()
    {
        $inviter_uid = $this->request->post("inviter_uid", 0, "intval");
        $invite_product_id = $this->request->post("invite_product_id", 0, "intval");
        $amount = $this->request->post("amount", 0, "floatval");

        Tools::addLog("invite", 'createOrder params: ' . $this->request->getInput());

        if (empty($inviter_uid) || empty($invite_product_id) || empty($amount)) {
            return $this->outJson(100, "参数错误");
        }

        if ($inviter_uid == $this->user_id) {
            return $this->outJson(100, "自己不能邀请自己");
        }

        $user = TMember::where('user_id', $this->user_id)->find();
        if (empty($user)) {
            return $this->outJson(100, "查询不到用户");
        }

        if ($user->is_broadcaster == TMember::IS_BROADCASTER_YES) {
            return $this->outJson(100, "您已经是主播");
        }

        $inviter_user = TMember::where('user_id', $inviter_uid)->find();
        if (empty($inviter_user)) {
            return $this->outJson(100, "邀请用户不存在");
        }

        $product = TInviteProduct::find($invite_product_id);
        if (empty($product)) {
            return $this->outJson(100, "开播产品不存在");
        }

        Db::startTrans();
        try {
            $now_time = date('Y-m-d H:i:s');
            $order = new TInviteOrder();
            $order_data = [
                'order_no' => TInviteOrder::createOrderNo('YGIV'),
                'inviter_uid' => $inviter_uid,
                'amount' => $amount,
                'reward_amount' => $product->reward_amount,
                'invite_product_id' => $product->id,
                'invite_product_snapshot' => json_encode($product->toArray()),
                'state' => TInviteOrder::STATE_UNPAY,
                'create_time' => $now_time,
                'update_time' => $now_time,
            ];
            $order->save($order_data);

            $relation = new TInviteRelation();
            $relation_data = [
                'inviter_uid' => $inviter_uid,
                'user_id' => $this->user_id,
                'invite_order_id' => $order->id,
                'create_time' => $now_time,
                'update_time' => $now_time,
            ];
            $relation->save($relation_data);
            Db::commit();
            return $this->outJson(0, "success", ["order_id" => $order->order_no]);
        } catch (\Exception $ex) {
            Db::rollback();
            return $this->outJson(500, "接口异常:" . $ex->getMessage());
        }

    }

    /**
     * 是否已经是主播
     */
    public function isBroadcaster()
    {
        $user = TMember::where('user_id', $this->user_id)->field('is_broadcaster')->find();
        $result = isset($user->is_broadcaster) && $user->is_broadcaster == TMember::IS_BROADCASTER_YES ? 1 : 0;
        return $this->outJson(0, "成功", $result);
    }
    
}
