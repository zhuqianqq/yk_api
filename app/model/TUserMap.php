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
    public static function getMallUserId($user_id)
    {
        $mall_user_id = self::where("user_id",$user_id)->value("mall_user_id");

        return intval($mall_user_id);
    }

    /**
     * 建立映射关系
     * @param int $user_id
     * @param int $mall_user_id
     */
    public static function addMap($user_id,$mall_user_id)
    {
        $user_map = new TUserMap();

        return $user_map->insertGetId([
            'user_id' => $user_id,
            "mall_user_id" => $mall_user_id,
        ]);
    }
}
