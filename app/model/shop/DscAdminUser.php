<?php
/**
 * 后台用户表
 */
namespace app\model\shop;

use app\model\shop\ShopBaseModel;
use think\facade\Db;

class DscAdminUser extends ShopBaseModel
{
    protected $table = "dsc_admin_user";

    /**
     * @var array 支付状态
     */
    public static $PAY_STATUS_ARR = [
        self::PAY_STATUS_WAIT_PAY => "待支付",
        self::PAY_STATUS_SUCCESS => "支付成功",
        self::PAY_STATUS_FAIL => "支付失败",
        self::PAY_STATUS_CANCEL => "支付取消",
    ];
}
