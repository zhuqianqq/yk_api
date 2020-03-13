<?php

namespace app\model\shop;


class MallShopUsers extends MallBaseModel
{
    protected $table = "mall_shop_users";

    public static function getShopId($userId)
    {
        $item = self::where("userId", $userId)->find();
        if ($item = null) {
            return 0;
        }
        return $item->shopId;
    }
}