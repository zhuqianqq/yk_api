<?php
/**
 * 商城平台商品表
 */

namespace app\model\shop;

use app\model\shop\ShopBaseModel;
use app\model\TUserMap;
use think\facade\Db;

class MallGoods extends MallBaseModel
{
    protected $table = "mall_goods";

    public static function getSellerGoods($user_id)
    {
        $mall_user_id = TUserMap::getMallUserId($user_id);
        $shop_id = mallShopUsers::getShopId($mall_user_id);
        if ($shop_id <= 0) {
            return null;
        }
        $products = self::where("shopId", $shop_id)->select();
        return $products;
    }

    public static function getRecommandGoods($data)
    {
        $products = self::where("goodsId", "in", $data)->select();
        return $products;
    }
}
