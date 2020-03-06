<?php
/**
 * 邀请页邀请关系
 */

namespace app\model;



class TInviteRelation extends BaseModel
{
    protected $table = "t_invite_relation";

    public static function getInviterByUid($user_id)
    {
        $relations = TInviteRelation::where(['user_id' => $user_id])->select()->toArray();
        if (empty($relations)) {
            return [];
        }
        $map = array_column($relations,'inviter_uid', 'invite_order_id');
        $orderIds = array_keys($map);
        $state = TInviteOrder::STATE_PAYED;
        $order = TInviteOrder::where(" id in (" . implode(',', $orderIds) . ") and `state` = {$state} ")->field('id')->find()->toArray();
        print_r($order);
        if (empty($order)) {
            return [];
        }
        $inviter_uid = isset($map[$order['id']]) ? $map[$order['id']] : 0;
        if (empty($inviter_uid)) {
            return [];
        }
        $user = TMember::where(['user_id' => $inviter_uid])->find()->toArray();
        print_r($user);
        return !empty($user) ? $user : [];
    }

}
