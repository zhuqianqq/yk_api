<?php
/**
 * 商城用户表
 */
namespace app\model\shop;

use app\model\shop\ShopBaseModel;
use think\facade\Db;

class DscUser extends ShopBaseModel
{
    protected $table = "dsc_users";

    /**
     * 获取商城用户信息
     * @param int $user_id
     * @param string $field
     */
    public static function getInfoByUserId($user_id,$field = "*")
    {
        return self::where("user_id",$user_id)->field($field)->find();
    }

    /**
     * 获取商城用户信息
     * @param string $user_name 用户名或手机号
     * @param string $field
     */
    public static function getInfoByUserName($user_name,$field = "*")
    {
        return self::where("user_name",$user_name)->field($field)->find();
    }
}
