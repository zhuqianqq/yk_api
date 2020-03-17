<?php
/**
 * 商城平台商品表
 */

namespace app\model\shop;

use app\model\shop\ShopBaseModel;
use think\facade\Db;

class MallGoods extends MallBaseModel
{
    protected $table = "mall_goods";

    public static function getSellerGoods($user_id)
    {
        $shop_id = mallShopUsers::getShopId($user_id);
        if ($shop_id <= 0) {
            return null;
        }
        $products = self::where("shopId", $shop_id)->select();
        return $products;
    }

    public static function getRecommandGoods($data)
    {
        $products = [];
        if (empty($data)) {
            return $data;
        }
        foreach ($data as $id) {
            $goods = self::where("goodsId", "=", $id)->limit(1)->find();
            if (empty($goods)) {
                continue;
            }
            $goods->goodsImg = stristr($goods->goodsImg,'http')?:$_SERVER['HTTP_HOST']. '/' . $goods->goodsImg;
            $products[] = $goods;
        }
        return $products;
    }
}
