<?php


namespace app\model;


use function Sodium\add;

class TProductRecommend extends BaseModel
{

    protected $table = "t_product_recommend";

    public static function addRecommendProduct($user_id, $prod_id)
    {
        $data = TProductRecommend::where("user_id", $user_id)->order("create_time", "desc")->select()->toArray();
        if (!empty($data) && count($data) >= 2) {
            return false;
        }
        $ids = array();
        $needAdd = false;
        foreach ($data as $k => $v) {
            if ($v['user_id'] == $user_id && $v['product_id'] == $prod_id && $k == 0) {

            } else {
                if ($k < 1) {
                    $needAdd = true;
                } else {
                    $needAdd = true;
                    array_push($ids, $v['id']);
                }
            }
        }
        if (empty($data) || $needAdd == true) {
            $recommendItem = new TProductRecommend();
            $recommendItem->user_id = $user_id;
            $recommendItem->product_id = $prod_id;
            $recommendItem->create_time = date("Y-m-d H:i:s");
            $recommendItem->save();
        }
        TProductRecommend::destroy($ids);
        return $data;
    }

    public static function removeRecommend($user_id, $prod_id)
    {
        return TProductRecommend::where(["user_id" => $user_id, "product_id" => $prod_id])->order("create_time", "desc")->delete();  
    }

    public static function getRecommendList($user_id)
    {
        $data = TProductRecommend::where("user_id", $user_id)->field("product_id")->order("create_time", "desc")->limit(2)->select();
        $ids = array();
        foreach ($data as $k => $v) {
            array_push($ids, $v['product_id']);
        }
        return $ids;
    }
}