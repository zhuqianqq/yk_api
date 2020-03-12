<?php
/**
 * 直播用户与商城用户映射关系表
 */
namespace app\model;

use think\facade\Db;

class TUserMap extends BaseModel
{
    protected $table = "t_user_map";

    /**
     * 获取商城用户id
     * @param int $user_id
     * @return int
     */
    public static function getShopUserId($user_id)
    {
        $shop_user_id = self::where("user_id",$user_id)->value("shop_user_id");

        return intval($shop_user_id);
    }
}
