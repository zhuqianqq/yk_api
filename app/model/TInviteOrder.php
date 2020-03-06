<?php
/**
 * 邀请页邀请订单
 */

namespace app\model;



class TInviteOrder extends BaseModel
{
    const STATE_UNPAY = 0; // 未支付
    const STATE_PAYED = 1; // 已支付

    public static $stateLabels = [
        self::STATE_UNPAY => '未支付',
        self::STATE_PAYED => '已支付',
    ];

    protected $table = "t_invite_order";

    public static function createOrderNo($source)
    {
        return strtoupper($source) . date('YmdHis') . self::randomkeys(8);
    }

    public static function randomkeys($length)
    {
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyz';
        $key = '';
        for($i=0;$i<$length;$i++)   
        {   
            $key .= $pattern{mt_rand(0,35)};    //生成php随机数
        }   
        return $key;
    }   
}
